<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llama a todos los seeders que necesitemos en el orden correcto.
        $this->call([
            UserSeeder::class,
            PropertySeeder::class, // <--- AÑADE ESTA LÍNEA

            // Aquí, en el futuro, podríamos añadir: PropertySeeder::class, ProspectSeeder::class, etc.
        ]);
    }
}
