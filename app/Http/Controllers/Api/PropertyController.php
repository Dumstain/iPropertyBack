<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\SearchHistory;
use App\Models\Prospect;

class PropertyController extends Controller
{
    /**
     * Muestra una lista de todas las propiedades del agente autenticado.
     * Ruta: GET /api/properties
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        // Obtenemos solo las propiedades que no han sido borradas (soft delete)
        $properties = $user->properties()->latest()->paginate(15);
        return response()->json($properties);
    }

    /**
     * Guarda una nueva propiedad en la base de datos.
     * Ruta: POST /api/properties
     */
public function store(Request $request): JsonResponse
{
    \Log::info('Solicitud POST recibida en store:', ['data' => $request->all(), 'token' => $request->header('Authorization')]);

    try {
        // 1. Validación exhaustiva para garantizar la calidad de los datos
        $validatedData = $request->validate($this->validationRules());

        // 2. Asignar el agente que está listando la propiedad
        $validatedData['listed_by_user_id'] = Auth::id();

        // 3. Lógica de negocio: Calcular el monto de la comisión si aplica
        if (isset($validatedData['precio_publicado']) && isset($validatedData['comision_pct'])) {
            $validatedData['comision_monto'] = $validatedData['precio_publicado'] * ($validatedData['comision_pct'] / 100);
        }

        // 4. Crear la propiedad
        $property = Property::create($validatedData);

        // 5. Buscar prospectos sugeridos para la propiedad recién creada
        $suggestions = $this->suggestedProspects($property)->getData();

        // 6. Devolver la propiedad creada y las sugerencias
        return response()->json([
            'property' => $property,
            'suggested_prospects' => $suggestions,
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validación fallida en store:', ['errors' => $e->errors()]);
        return response()->json(['error' => $e->errors()], 422);
    } catch (\Exception $e) {
        \Log::error('Error en store:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Error interno al crear la propiedad', 'details' => $e->getMessage()], 500);
    }
}

    /**
     * Muestra los detalles de una propiedad específica.
     * Ruta: GET /api/properties/{property}
     */
    public function show(Property $property): JsonResponse
    {
        // Seguridad: Asegurarse de que el agente solo vea sus propias propiedades
        if (Auth::id() !== $property->listed_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($property);
    }

    /**
     * Actualiza una propiedad existente.
     * Ruta: PUT /api/properties/{property}
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        // 1. Seguridad: Verificar propiedad
        if (Auth::id() !== $property->listed_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // 2. Validar los datos de entrada (con 'sometimes' para permitir actualizaciones parciales)
        $validatedData = $request->validate($this->validationRules('sometimes'));

        // 3. Lógica de negocio: Recalcular la comisión si el precio o el % cambian
        if (isset($validatedData['precio_publicado']) || isset($validatedData['comision_pct'])) {
            $newPrice = $validatedData['precio_publicado'] ?? $property->precio_publicado;
            $newPct = $validatedData['comision_pct'] ?? $property->comision_pct;
            if ($newPrice && $newPct) {
                $validatedData['comision_monto'] = $newPrice * ($newPct / 100);
            }
        }

        // 4. Actualizar la propiedad
        $property->update($validatedData);

        return response()->json($property);
    }

    /**
     * Elimina una propiedad (Soft Delete).
     * Ruta: DELETE /api/properties/{property}
     */
    public function destroy(Property $property): JsonResponse
    {
        // Seguridad: Verificar propiedad
        if (Auth::id() !== $property->listed_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Usamos Soft Delete, la propiedad no se borra permanentemente
        $property->delete();

        return response()->json(null, 204);
    }

    /**
     * Reglas de validación reutilizables para store y update.
     */
    private function validationRules($ruleType = 'required'): array
    {
        return [
            'nombre_comercial' => [$ruleType, 'string', 'max:255'],
            'estado' => [$ruleType, 'in:disponible,apartada,vendida,rentada,pausada'],
            'tipo_contrato' => ['nullable', 'in:exclusiva,directa,abierta'],
            'precio_publicado' => [$ruleType, 'numeric', 'min:0'],
            'notas_precio' => ['nullable', 'string', 'max:255'],
            'comision_pct' => ['nullable', 'numeric', 'between:0,100'],
            'comision_notas' => ['nullable', 'string'],
            'domicilio_calle' => [$ruleType, 'string', 'max:255'],
            'domicilio_num_ext' => [$ruleType, 'string', 'max:50'],
            'domicilio_num_int' => ['nullable', 'string', 'max:50'],
            'colonia' => [$ruleType, 'string', 'max:255'],
            'ciudad' => [$ruleType, 'string', 'max:255'],
            'estado_republica' => [$ruleType, 'string', 'max:255'],
            'colindancias' => ['nullable', 'string'],
            'm2_terreno' => ['nullable', 'numeric', 'min:0'],
            'm2_construccion' => ['nullable', 'numeric', 'min:0'],
            'num_pisos' => [$ruleType, 'integer', 'min:0'],
            'habitaciones_total' => [$ruleType, 'integer', 'min:0'],
            'habitaciones_pb' => [$ruleType, 'integer', 'min:0'],
            'banos_completos' => [$ruleType, 'integer', 'min:0'],
            'medios_banos' => [$ruleType, 'integer', 'min:0'],
            'banos_completos_pb' => [$ruleType, 'integer', 'min:0'],
            'medios_banos_pb' => [$ruleType, 'integer', 'min:0'],
            'jardin_tamano' => ['nullable', 'in:chico,mediano,grande'],
            'amenidades' => ['nullable', 'array'],
            'otros_detalles' => ['nullable', 'string'],
            'imagenes' => ['nullable', 'array'],
        ];
    }



    /**
     * Encuentra y devuelve una lista de prospectos compatibles con una propiedad específica.
     * VERSIÓN DEFINITIVA USANDO LA RELACIÓN latestSearch().
     */
    public function suggestedProspects(Property $property): JsonResponse
    {
        // Seguridad:
        if (Auth::id() !== $property->listed_by_user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // 1. Obtener prospectos activos que TENGAN una búsqueda, y cargar esa búsqueda más reciente.
        $prospects = Prospect::where('added_by_user_id', Auth::id())
            ->where('status', 'activo')
            ->whereHas('latestSearch') // Solo trae prospectos que tengan al menos una búsqueda.
            ->with('latestSearch')    // Carga la relación que acabamos de crear.
            ->get();

        $suggestedProspects = [];

        // 2. Iterar sobre los prospectos
        foreach ($prospects as $prospect) {
            // No hay necesidad de buscar la última búsqueda, ¡ya viene cargada!
            $criteria = $prospect->latestSearch->extracted_criteria;
            $score = $property->calculateMatchScore($criteria);

            if ($score >= 60) {
                $suggestedProspects[] = [
                    'prospect'      => $prospect->unsetRelation('latestSearch'),
                    'match_score'   => $score,
                    'last_query'    => $prospect->latestSearch->query_text,
                ];
            }
        }

        // 3. Ordenar
        usort($suggestedProspects, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return response()->json($suggestedProspects);
    }
}
