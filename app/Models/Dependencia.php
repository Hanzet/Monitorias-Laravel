<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    $protected $table = 'dependencias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    public function monitorias()
    {
        return $this->hasMany(Monitoria::class);
    }
}
