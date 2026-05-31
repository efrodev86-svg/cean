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
        'bimestre',
        'calificacion',
        'faltas',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'decimal:1',
            'bimestre' => 'integer',
            'faltas' => 'integer',
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
