<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justificaciones extends Model
{
    protected $table = 'justificaciones';

    protected $fillable = [
        'monitoria_id',
        'persona_id',
        'periodo_academico_id',
        'descripcion',
        'tipos_justificacion_id',
        'descripcion',
        'fecha',
        'estado'
    ];

    
}