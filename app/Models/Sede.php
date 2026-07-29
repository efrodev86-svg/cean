<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    protected $table = 'sedes';

    protected $fillable = [
        'nombre',
        'clave',
        'escuela',
        'director',
        'ciudad',
        'logo',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function ciclos(): HasMany
    {
        return $this->hasMany(CicloEscolar::class);
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function encargados(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', User::rolesEncargado());
    }

    /**
     * Docentes asignados a la sede (relación muchos-a-muchos).
     */
    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->whereIn('role', User::rolesDocente())
            ->withTimestamps();
    }

    /**
     * Ciclo escolar activo de esta sede (cada sede gestiona los suyos por separado).
     */
    public function cicloActivo(): ?CicloEscolar
    {
        return $this->ciclos()->where('activo', true)->first();
    }

    /**
     * Valores institucionales para la boleta, con fallback a config('boleta').
     *
     * @return array{escuela: string, director: string, ciudad: string, logo: ?string}
     */
    public function datosBoleta(): array
    {
        return [
            'escuela' => $this->escuela ?: config('boleta.escuela'),
            'director' => $this->director ?: config('boleta.director'),
            'ciudad' => $this->ciudad ?: config('boleta.ciudad'),
            'logo' => $this->logo ?: config('boleta.logo_ensq'),
        ];
    }
}
