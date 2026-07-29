<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = [
        'alumno_id',
        'materia_id',
        'semestre',
        'calificacion',
        'asistencia_porcentaje',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'decimal:1',
            'semestre' => 'integer',
            'asistencia_porcentaje' => 'integer',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }
}
