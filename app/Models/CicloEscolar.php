<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloEscolar extends Model
{
    protected $table = 'ciclos_escolares';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    public static function activo(): ?self
    {
        return static::query()->where('activo', true)->first();
    }
}
