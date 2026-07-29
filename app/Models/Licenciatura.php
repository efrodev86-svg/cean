<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Licenciatura extends Model
{
    protected $fillable = [
        'nombre_corto',
        'clave_dgp',
        'nombre',
        'plan_nombre',
        'anio_plan',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'anio_plan' => 'integer',
        ];
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }
}
