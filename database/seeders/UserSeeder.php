<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos create para crear un único usuario.
        // Nos aseguramos de que no exista para evitar duplicados si ejecutamos el seeder varias veces.
        User::firstOrCreate(
            ['email' => 'rodrigo@example.com'], // Busca un usuario con este email
            [
                'name' => 'Rodrigo Farfán',
                'password' => Hash::make('rodrigo123'), // ¡IMPORTANTE! Siempre encriptar la contraseña.
                'role' => 'agent'
            ]
        );
    }
}
