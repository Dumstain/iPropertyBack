<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'listed_by_user_id', 'nombre_comercial', 'estado', 'tipo_contrato',
        'precio_publicado', 'notas_precio', 'comision_pct', 'comision_monto', 'comision_notas',
        'domicilio_calle', 'domicilio_num_ext', 'domicilio_num_int', 'colonia', 'ciudad', 'estado_republica',
        'colindancias', 'm2_terreno', 'm2_construccion', 'num_pisos',
        'habitaciones_total', 'habitaciones_pb', 'banos_completos', 'medios_banos',
        'banos_completos_pb', 'medios_banos_pb', 'jardin_tamano',
        'amenidades', 'otros_detalles', 'imagenes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amenidades' => 'array',
        'imagenes' => 'array',
    ];

    // --- RELACIONES ---

    /** Una propiedad es listada por un usuario (agente). */
    public function listedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'listed_by_user_id');
    }

    /**
     * Calcula el % de compatibilidad entre una propiedad y los criterios.
     * VERSIÓN MEJORADA: Puntuación normalizada por criterio, pesos ponderados, matching gradual.
     * Agrega match neutral (50%) para criterios NO especificados, para evitar scores bajos en queries simples.
     * Incluye bono fuerte para habitaciones en PB si se indica necesidad de accesibilidad.
     */
    public function calculateMatchScore(array $criteria): float
    {
        $totalScore = 0.0;
        $weights = [
            'precio' => 30,      // 30%
            'ubicacion' => 25,   // 25%
            'habitaciones' => 20, // 20% (incluye PB con bono extra si se necesita)
            'banos' => 10,       // 10%
            'jardin' => 10,      // 10%
            'amenidades' => 5    // 5%
        ];

        // Precio (30%): Matching continuo, o neutral si no especificado
        if (isset($criteria['precio_min']) || isset($criteria['precio_max'])) {
            $priceMin = (float) ($criteria['precio_min'] ?? 0);
            $priceMax = (float) ($criteria['precio_max'] ?? PHP_INT_MAX);
            $propertyPrice = (float) $this->precio_publicado;

            if ($propertyPrice >= $priceMin && $propertyPrice <= $priceMax) {
                $priceMatch = 100; // Perfecto
            } else {
                $deviation = 0;
                if ($propertyPrice < $priceMin) {
                    $deviation = ($priceMin - $propertyPrice) / $priceMin;
                } elseif ($propertyPrice > $priceMax) {
                    $deviation = ($propertyPrice - $priceMax) / $priceMax;
                }
                $priceMatch = max(0, 100 - ($deviation * 200)); // Penaliza gradualmente
            }
            $totalScore += ($priceMatch / 100) * $weights['precio'];
        } else {
            $totalScore += 0.5 * $weights['precio']; // Neutral 50%
        }

        // Ubicación (25%): Fuzzy matching, o neutral si no especificado
        if (!empty($criteria['ubicaciones'])) {
            $bestMatch = 0;
            foreach ((array) $criteria['ubicaciones'] as $location) {
                similar_text(strtolower($this->colonia ?? ''), strtolower($location), $percent);
                if ($percent > $bestMatch) {
                    $bestMatch = $percent;
                }
            }
            $locationMatch = ($bestMatch >= 80) ? $bestMatch : 0; // Umbral estricto
            $totalScore += ($locationMatch / 100) * $weights['ubicacion'];
        } else {
            $totalScore += 0.5 * $weights['ubicacion']; // Neutral 50%
        }

        // Habitaciones (20% base + bono para PB): Escala proporcional, o neutral si no especificado
        if (isset($criteria['habitaciones_total'])) {
            $requestedHab = (int) ($criteria['habitaciones_total'] ?? 0);
            $propertyHab = (int) ($this->habitaciones_total ?? 0);
            $habMatch = 0;
            if ($requestedHab > 0) {
                $ratio = $propertyHab / $requestedHab;
                $habMatch = ($ratio >= 1) ? 100 : max(0, $ratio * 100); // Penaliza <100%, tolera exceso
            }
            $totalScore += ($habMatch / 100) * ($weights['habitaciones'] * 0.7); // 70% para total

            // Bono PB: Fuerte si necesidad_pb true, menor si solo habitaciones_pb
            $pbBonusWeight = $weights['habitaciones'] * 0.3;
            $pbMatch = 0;
            $hasPbNeed = isset($criteria['necesidad_pb']) && $criteria['necesidad_pb'];
            if ($hasPbNeed || isset($criteria['habitaciones_pb'])) {
                $requestedPb = (int) ($criteria['habitaciones_pb'] ?? 1);
                $propertyPb = (int) ($this->habitaciones_pb ?? 0);
                $pbMatch = ($propertyPb >= $requestedPb) ? 100 : 0;
                if ($hasPbNeed && !isset($criteria['habitaciones_pb'])) {
                    $requestedPb = 1; // Mínimo 1 para accesibilidad
                }
                if (!$hasPbNeed) $pbBonusWeight *= 0.5; // Menor si no crítico
            }
            $totalScore += ($pbMatch / 100) * $pbBonusWeight;
        } else {
            $totalScore += 0.5 * $weights['habitaciones']; // Neutral 50%
        }

        // Baños (10%): >= con escala, o neutral si no especificado
        if (isset($criteria['banos_total'])) {
            $requestedBan = (int) ($criteria['banos_total'] ?? 0);
            $propertyBan = (int) ($this->banos_completos ?? 0);
            $banMatch = ($propertyBan >= $requestedBan) ? 100 : max(0, ($propertyBan / $requestedBan) * 100);
            $totalScore += ($banMatch / 100) * $weights['banos'];
        } else {
            $totalScore += 0.5 * $weights['banos']; // Neutral 50%
        }

        // Jardín (10%): Escala por tamaño, o neutral si no especificado
        if (isset($criteria['jardin_tamano']) && $criteria['jardin_tamano'] !== 'ninguno') {
            $requestedGarden = $criteria['jardin_tamano'];
            $propertyGarden = $this->jardin_tamano ?? 'ninguno';
            $gardenMatch = 0;
            if ($requestedGarden === 'cualquiera' && $propertyGarden !== 'ninguno') {
                $gardenMatch = 100;
            } else {
                $gardenMap = ['chico' => 1, 'mediano' => 2, 'grande' => 3]; // Ajustado a 'chico' como en validación
                $reqSize = $gardenMap[$requestedGarden] ?? 0;
                $propSize = $gardenMap[$propertyGarden] ?? 0;
                $gardenMatch = ($reqSize > 0 && $propSize >= $reqSize) ? 100 : max(0, ($propSize / $reqSize) * 100);
            }
            $totalScore += ($gardenMatch / 100) * $weights['jardin'];
        } else {
            $totalScore += 0.5 * $weights['jardin']; // Neutral 50%
        }

        // Amenidades (5%): Porcentaje de cobertura, o neutral si no especificado
        if (!empty($criteria['amenidades']) && is_array($criteria['amenidades']) && is_array($this->amenidades)) {
            $requestedAmen = array_unique((array) $criteria['amenidades']);
            $matchedAmen = count(array_intersect($requestedAmen, $this->amenidades));
            $amenMatch = (count($requestedAmen) > 0) ? ($matchedAmen / count($requestedAmen)) * 100 : 0;
            $totalScore += ($amenMatch / 100) * $weights['amenidades'];
        } else {
            $totalScore += 0.5 * $weights['amenidades']; // Neutral 50%
        }

        return min($totalScore, 100.0);
    }
}
