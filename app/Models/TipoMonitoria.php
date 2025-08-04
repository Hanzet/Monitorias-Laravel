<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMonitoria extends Model
{
    protected $table = 'tipos_monitoria';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];
}
