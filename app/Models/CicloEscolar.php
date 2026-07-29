<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloEscolar extends Model
{
    protected $table = 'ciclos_escolares';

    protected $fillable = [
        'sede_id',
        'nombre',
        'activo',
        'fecha_emision_boletas',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_emision_boletas' => 'date',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class)->orderBy('clave');
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }

    /**
     * Primer ciclo activo (legado). Con varias sedes hay un ciclo activo por sede;
     * usa activoParaSede() cuando el contexto de sede importa.
     */
    public static function activo(): ?self
    {
        return static::query()->where('activo', true)->first();
    }

    public static function activoParaSede(?int $sedeId): ?self
    {
        return static::query()
            ->where('activo', true)
            ->when($sedeId, fn ($q) => $q->where('sede_id', $sedeId))
            ->first();
    }

    /**
     * Periodo (A/B) que corresponde a la paridad del semestre indicado.
     */
    public function periodoParaSemestre(int $semestre): ?Periodo
    {
        return $this->periodos->first(fn (Periodo $periodo) => $periodo->aplicaASemestre($semestre));
    }

    /**
     * Crea (si no existen) los dos periodos predeterminados A y B del ciclo.
     */
    public function crearPeriodosPredeterminados(): void
    {
        foreach (['A' => 'Periodo A · Agosto–Enero', 'B' => 'Periodo B · Febrero–Julio'] as $clave => $nombre) {
            $this->periodos()->firstOrCreate(
                ['clave' => $clave],
                ['nombre' => $nombre]
            );
        }
    }
}
