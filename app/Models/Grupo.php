<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    protected $fillable = [
        'sede_id',
        'ciclo_escolar_id',
        'semestre',
        'letra',
        'licenciatura',
        'nombre',
    ];

    protected function casts(): array
    {
        return [
            'semestre' => 'integer',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(GrupoMateriaDocente::class);
    }

    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'grupo_materia_docente')
            ->withPivot('docente_id')
            ->withTimestamps();
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'grupo_materia_docente', 'grupo_id', 'docente_id')
            ->withPivot('materia_id')
            ->withTimestamps();
    }

    public function clave(): string
    {
        return "{$this->semestre}{$this->letra}";
    }

    public function etiqueta(): string
    {
        return "{$this->semestre}°-{$this->letra} · {$this->licenciatura}";
    }

    public function licenciaturaCatalogo(): ?Licenciatura
    {
        return Licenciatura::query()
            ->get()
            ->first(fn (Licenciatura $licenciatura) => self::normalizarClave($licenciatura->nombre_corto) === self::normalizarClave($this->licenciatura));
    }

    /**
     * Materias del plan que corresponden al semestre y licenciatura del grupo.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Materia>
     */
    public function materiasDelPlan()
    {
        $licenciatura = $this->licenciaturaCatalogo();

        if (! $licenciatura) {
            return Materia::query()->whereRaw('1 = 0')->get();
        }

        return Materia::query()
            ->where('licenciatura_id', $licenciatura->id)
            ->where('semestre', $this->semestre)
            ->orderBy('orden')
            ->orderBy('clave')
            ->get();
    }

    public static function construirNombre(int $semestre, string $letra, string $licenciatura): string
    {
        return "{$semestre}°-{$letra} · {$licenciatura}";
    }

    public static function normalizarClave(string $valor): string
    {
        $valor = mb_strtoupper(trim($valor));

        return strtr($valor, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'Ü' => 'U', 'Ñ' => 'N',
        ]);
    }
}
