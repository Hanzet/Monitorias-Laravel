<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoJustificaciones extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('tipos_justificaciones')->insert([
            ['nombre' => 'Enfermedad', 'descripcion' => 'Justificación por enfermedad'],
            ['nombre' => 'Compromiso personal', 'descripcion' => 'Justificación por compromiso personal'],
            ['nombre' => 'Problemas técnicos', 'descripcion' => 'Justificación por problemas técnicos'],
            ['nombre' => 'Otro', 'descripcion' => 'Otra justificación']
        ]);
    }
}
