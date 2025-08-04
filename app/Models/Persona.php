<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    protected $table = 'persona';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'nombreA',
        'nombreB',
        'apellidoA',
        'apellidoB',
        'correo_electronico',
        'telefono',
        'fecha_nacimiento',
        'direccion',
        'estado'
    ];

    public function users(): hasMany
    {
        return $this->hasMany(User::class, 'persona_id');
    }
}
