<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradoAcademico extends Model
{
    protected $table = 'grados_academicos';

    protected $fillable = [
        'abreviatura',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function docentes(): HasMany
    {
        return $this->hasMany(User::class, 'grado_academico_id');
    }

    public function etiqueta(): string
    {
        return $this->abreviatura;
    }
}
