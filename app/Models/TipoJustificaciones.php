<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoJustificaciones extends Model
{
    protected $table = 'tipos_justificaciones';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // Tipos de justificaciones tiene muchas justificaciones con justificaciones (uno a muchos)
    public function justificaciones(): hasMany
    {
        return $this->hasMany(Justificaciones::class, 'tipos_justificacion_id');
    }
}
