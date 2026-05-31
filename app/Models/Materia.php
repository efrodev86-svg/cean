<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $fillable = [
        'nombre',
        'clave',
        'grado',
    ];

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }
}
