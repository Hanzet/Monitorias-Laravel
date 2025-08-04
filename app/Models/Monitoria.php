<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitoria extends Model
{
    $protected $table = 'monitorias';

    protected $fillable = [
        'persona_id',
        'tipo_monitoria_id',
        'dependencia_id',
        'periodo_academico_id',
        'descripcion',
        'inicio',
        'fin',
        'horas_asignadas',
        'estado'
    ];

    // 
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function tipoMonitoria(): BelongsTo
    {
        return $this->belongsTo(TipoMonitoria::class, 'tipo_monitoria_id');
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_academico_id');
    }
}
