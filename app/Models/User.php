<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'nombre', 'primer_apellido', 'segundo_apellido', 'email', 'curp', 'password', 'role', 'activo', 'codigo', 'grado_academico_id', 'tipo_contratacion', 'clave_plaza', 'nombre_plaza', 'celular', 'sede_id', 'alumno_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ENCARGADO = 'encargado';

    public const ROLE_DOCENTE = 'docente';

    public const ROLE_ENCARGADO_DOCENTE = 'encargado-docente';

    public const ROLE_ALUMNO = 'alumno';

    /**
     * @return list<string>
     */
    public static function rolesEncargado(): array
    {
        return [self::ROLE_ENCARGADO, self::ROLE_ENCARGADO_DOCENTE];
    }

    /**
     * @return list<string>
     */
    public static function rolesPersonalControl(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_ENCARGADO, self::ROLE_ENCARGADO_DOCENTE];
    }

    /**
     * @return list<string>
     */
    public static function rolesDocente(): array
    {
        return [self::ROLE_DOCENTE, self::ROLE_ENCARGADO_DOCENTE];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    /**
     * Sedes en las que el docente imparte clases (un docente puede estar en varias).
     */
    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class)->withTimestamps();
    }

    public function gradoAcademico(): BelongsTo
    {
        return $this->belongsTo(GradoAcademico::class);
    }

    public function estudiosCursados(): HasMany
    {
        return $this->hasMany(EstudioCursado::class);
    }

    public function asignacionesDocente(): HasMany
    {
        return $this->hasMany(GrupoMateriaDocente::class, 'docente_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function nombreCompleto(): string
    {
        $partes = array_filter([
            $this->nombre,
            $this->primer_apellido,
            $this->segundo_apellido,
        ], fn (?string $parte) => filled($parte));

        if ($partes !== []) {
            return implode(' ', $partes);
        }

        return $this->name;
    }

    public static function nombreCompletoDesdePartes(array $datos): string
    {
        return collect([
            $datos['nombre'] ?? null,
            $datos['primer_apellido'] ?? null,
            $datos['segundo_apellido'] ?? null,
        ])->filter(fn (?string $parte) => filled($parte))->implode(' ');
    }

    public function nombreConGrado(): string
    {
        $nombre = $this->nombreCompleto();

        if (! $this->gradoAcademico) {
            return $nombre;
        }

        $prefijo = rtrim($this->gradoAcademico->abreviatura, '.');

        return $prefijo.'. '.$nombre;
    }

    public function tipoContratacionLabel(): ?string
    {
        if ($this->tipo_contratacion === null) {
            return null;
        }

        return config('cean.tipos_contratacion_docente.'.$this->tipo_contratacion, $this->tipo_contratacion);
    }

    /**
     * Administrador global de la institución (gestiona sedes, usuarios y catálogo).
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Encargado de control escolar de una sede (acceso acotado a su sede).
     */
    public function isEncargado(): bool
    {
        return in_array($this->role, self::rolesEncargado(), true);
    }

    /**
     * Encargado que además imparte clases (control escolar + portal docente).
     */
    public function isEncargadoDocente(): bool
    {
        return $this->role === self::ROLE_ENCARGADO_DOCENTE;
    }

    /**
     * Personal de control escolar: administrador global o encargado de sede.
     */
    public function isControlEscolar(): bool
    {
        return $this->isAdmin() || $this->isEncargado();
    }

    public function isDocente(): bool
    {
        return in_array($this->role, self::rolesDocente(), true);
    }

    public function docenteEstaActivo(): bool
    {
        if (! $this->isDocente()) {
            return true;
        }

        return (bool) $this->activo;
    }

    public function puedeAccederPortalDocente(): bool
    {
        return $this->isDocente() && $this->docenteEstaActivo();
    }

    public function isAlumno(): bool
    {
        return $this->role === self::ROLE_ALUMNO;
    }

    /**
     * Alumnos y encargados solo pueden cambiar su contraseña en /profile.
     */
    public function soloPuedeCambiarPasswordEnPerfil(): bool
    {
        return $this->isAlumno() || $this->isEncargado();
    }

    public function alumnoEstaActivo(): bool
    {
        if (! $this->isAlumno()) {
            return true;
        }

        return (bool) $this->activo;
    }

    public function puedeAccederPortalAlumno(): bool
    {
        return $this->isAlumno() && $this->alumnoEstaActivo();
    }

    public function scopeDocentes(Builder $query): Builder
    {
        return $query->whereIn('role', self::rolesDocente());
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_ENCARGADO => 'Encargado',
            self::ROLE_ENCARGADO_DOCENTE => 'Encargado-docente',
            self::ROLE_ALUMNO => 'Alumno',
            default => 'Docente',
        };
    }

    /**
     * Administrador sin sede asignada: tiene visibilidad de todas las sedes.
     */
    public function isAdminGlobal(): bool
    {
        return $this->isAdmin() && $this->sede_id === null;
    }

    /**
     * Sede a la que está acotado el usuario (null = sin restricción / global).
     */
    public function sedeScopeId(): ?int
    {
        if ($this->isAdmin()) {
            return null;
        }

        return $this->sede_id;
    }

    /**
     * Sincroniza la sede de encargado en el pivote docente (mínimo para impartir clases).
     */
    public function sincronizarSedeDocenteDesdeEncargado(): void
    {
        if (! $this->isEncargadoDocente() || $this->sede_id === null) {
            return;
        }

        $this->sedes()->syncWithoutDetaching([$this->sede_id]);
    }

    public function homeRoute(): string
    {
        if ($this->isControlEscolar()) {
            return route('admin.dashboard', absolute: false);
        }

        if ($this->isDocente()) {
            return route('docente.dashboard', absolute: false);
        }

        if ($this->isAlumno()) {
            return route('alumno.dashboard', absolute: false);
        }

        return route('home', absolute: false);
    }
}
