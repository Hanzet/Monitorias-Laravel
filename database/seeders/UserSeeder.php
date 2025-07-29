<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@ejemplo.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Usuario normal
        User::create([
            'name' => 'Usuario Normal',
            'email' => 'usuario@ejemplo.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Usuario de prueba
        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'prueba@ejemplo.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Crear usuarios adicionales usando factory
        User::factory(10)->create();
    }
} 