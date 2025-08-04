<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Persona;
use App\Models\TipoMonitoria;
use App\Models\Dependencia;
use App\Models\PeriodoAcademico;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonitoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_monitoria_con_datos_validos()
    {
        // Crear datos relacionados usando factories
        $persona = Persona::factory()->create();
        $tipo = TipoMonitoria::factory()->create();
        $dependencia = Dependencia::factory()->create();
        $periodo = PeriodoAcademico::factory()->create();

        // Datos válidos para la solicitud
        $payload = [
            'persona_id'            => $persona->id,
            'tipo_monitoria_id'     => $tipo->id,
            'dependencia_id'        => $dependencia->id,
            'periodo_academico_id'  => $periodo->id,
            'descripcion'           => 'Apoyo en laboratorio',
            'inicio'                => now()->toDateString(),
            'fin'                   => now()->addWeek()->toDateString(),
            'horas_asignadas'       => 10,
            'estado'                => 'activo'
        ];

        $response = $this->postJson('/api/monitorias', $payload);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Monitoria created successfully']);

        $this->assertDatabaseHas('monitorias', [
            'persona_id' => $persona->id,
            'descripcion' => 'Apoyo en laboratorio',
        ]);
    }

    public function test_no_crea_monitoria_con_datos_invalidos()
    {
        $payload = []; // vacío a propósito

        $response = $this->postJson('/api/monitorias', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'persona_id',
            'tipo_monitoria_id',
            'dependencia_id',
            'periodo_academico_id',
            'inicio',
            'fin',
            'horas_asignadas',
            'estado'
        ]);
    }
}
