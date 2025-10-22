<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\User;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiamos la tabla para evitar duplicados si corremos el seeder varias veces
        Property::truncate();

        // Buscamos al usuario Rodrigo para asignarle las propiedades
        $user = User::where('email', 'rodrigo@example.com')->first();

        if ($user) {
            $properties = [
                // --- Propiedad 1: La que ya probamos, ideal para búsquedas de Altozano ---
                [
                    'listed_by_user_id' => $user->id,
                    'nombre_comercial' => 'Casa Moderna en Vistas Altozano',
                    'estado' => 'disponible',
                    'tipo_contrato' => 'exclusiva',
                    'precio_publicado' => 4500000.00,
                    'comision_pct' => 3.5,
                    'comision_monto' => 157500.00,
                    'domicilio_calle' => 'Paseo del Alce', 'domicilio_num_ext' => '123',
                    'colonia' => 'Vistas Altozano', 'ciudad' => 'Morelia', 'estado_republica' => 'Michoacán',
                    'm2_terreno' => 160.00, 'm2_construccion' => 210.00,
                    'num_pisos' => 2, 'habitaciones_total' => 3, 'habitaciones_pb' => 1,
                    'banos_completos' => 2, 'medios_banos' => 1, 'banos_completos_pb' => 0, 'medios_banos_pb' => 1,
                    'jardin_tamano' => 'mediano',
                    'amenidades' => json_encode(['coto_privado', 'vigilancia_24h', 'terraza']),
                ],
                // --- Propiedad 2: Más económica, sin jardín, en otra zona ---
                [
                    'listed_by_user_id' => $user->id,
                    'nombre_comercial' => 'Departamento Céntrico y Acogedor',
                    'estado' => 'disponible',
                    'tipo_contrato' => 'directa',
                    'precio_publicado' => 2800000.00,
                    'comision_pct' => 3.0,
                    'comision_monto' => 84000.00,
                    'domicilio_calle' => 'Av. Madero Poniente', 'domicilio_num_ext' => '500',
                    'colonia' => 'Centro Histórico', 'ciudad' => 'Morelia', 'estado_republica' => 'Michoacán',
                    'm2_terreno' => 90.00, 'm2_construccion' => 90.00,
                    'num_pisos' => 1, 'habitaciones_total' => 2, 'habitaciones_pb' => 2, // Depa en un piso
                    'banos_completos' => 2, 'medios_banos' => 0, 'banos_completos_pb' => 2, 'medios_banos_pb' => 0,
                    'jardin_tamano' => null, // No tiene jardín
                    'amenidades' => json_encode(['elevador', 'seguridad']),
                ],
                // --- Propiedad 3: De lujo, con alberca y jardín grande ---
                [
                    'listed_by_user_id' => $user->id,
                    'nombre_comercial' => 'Residencia de Lujo en Club de Golf Tres Marías',
                    'estado' => 'disponible',
                    'tipo_contrato' => 'exclusiva',
                    'precio_publicado' => 9850000.00,
                    'comision_pct' => 4.0,
                    'comision_monto' => 394000.00,
                    'domicilio_calle' => 'Paseo del Jaguar', 'domicilio_num_ext' => '100',
                    'colonia' => 'Club de Golf Tres Marías', 'ciudad' => 'Morelia', 'estado_republica' => 'Michoacán',
                    'm2_terreno' => 400.00, 'm2_construccion' => 350.00,
                    'num_pisos' => 2, 'habitaciones_total' => 4, 'habitaciones_pb' => 0,
                    'banos_completos' => 4, 'medios_banos' => 1, 'banos_completos_pb' => 0, 'medios_banos_pb' => 1,
                    'jardin_tamano' => 'grande',
                    'amenidades' => json_encode(['alberca', 'vigilancia_24h', 'gimnasio', 'campo_de_golf']),
                ],
                // --- Propiedad 4: Opción similar a la 1 pero más barata y pequeña ---
                [
                    'listed_by_user_id' => $user->id,
                    'nombre_comercial' => 'Casa Familiar en Altozano La Nueva',
                    'estado' => 'disponible',
                    'tipo_contrato' => 'abierta',
                    'precio_publicado' => 3950000.00,
                    'comision_pct' => 3.0,
                    'comision_monto' => 118500.00,
                    'domicilio_calle' => 'Cerro de la Campana', 'domicilio_num_ext' => '45',
                    'colonia' => 'Altozano', 'ciudad' => 'Morelia', 'estado_republica' => 'Michoacán',
                    'm2_terreno' => 140.00, 'm2_construccion' => 180.00,
                    'num_pisos' => 2, 'habitaciones_total' => 3, 'habitaciones_pb' => 0,
                    'banos_completos' => 2, 'medios_banos' => 1, 'banos_completos_pb' => 0, 'medios_banos_pb' => 1,
                    'jardin_tamano' => 'chico',
                    'amenidades' => json_encode(['coto_privado']),
                ],
                // --- Propiedad 5: Terreno en venta, para probar otro tipo de propiedad ---
                [
                    'listed_by_user_id' => $user->id,
                    'nombre_comercial' => 'Terreno Residencial en El Monasterio',
                    'estado' => 'disponible',
                    'tipo_contrato' => 'exclusiva',
                    'precio_publicado' => 1900000.00,
                    'comision_pct' => 5.0,
                    'comision_monto' => 95000.00,
                    'domicilio_calle' => 'Callejón del Convento', 'domicilio_num_ext' => 'Lote 22',
                    'colonia' => 'El Monasterio', 'ciudad' => 'Morelia', 'estado_republica' => 'Michoacán',
                    'm2_terreno' => 250.00, 'm2_construccion' => 0,
                    'num_pisos' => 0, 'habitaciones_total' => 0, 'habitaciones_pb' => 0,
                    'banos_completos' => 0, 'medios_banos' => 0, 'banos_completos_pb' => 0, 'medios_banos_pb' => 0,
                    'jardin_tamano' => null,
                    'amenidades' => json_encode(['servicios_ocultos', 'coto_privado']),
                ],
            ];

            foreach ($properties as $property) {
                Property::create($property);
            }
        }
    }
}
