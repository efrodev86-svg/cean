<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoMateriaDocente extends Model
{
    protected $table = 'grupo_materia_docente';

    protected $fillable = [
        'grupo_id',
        'materia_id',
        'docente_id',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}
