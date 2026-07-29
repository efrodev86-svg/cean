<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $fillable = [
        'licenciatura_id',
        'nombre',
        'clave',
        'grado',
        'semestre',
        'orden',
        'creditos',
        'horas_semana',
        'horas_semestre',
    ];

    protected function casts(): array
    {
        return [
            'semestre' => 'integer',
            'orden' => 'integer',
            'creditos' => 'decimal:2',
            'horas_semana' => 'decimal:2',
            'horas_semestre' => 'decimal:2',
        ];
    }

    public function licenciatura(): BelongsTo
    {
        return $this->belongsTo(Licenciatura::class);
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(GrupoMateriaDocente::class);
    }

    public function etiquetaSemestre(): string
    {
        if ($this->semestre) {
            return $this->semestre.'°';
        }

        return $this->grado ?? '—';
    }
}
