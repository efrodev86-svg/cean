<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumno extends Model
{
    protected $fillable = [
        'matricula',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'grado',
        'grupo',
        'curp',
        'fecha_nacimiento',
        'ciclo_escolar_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
