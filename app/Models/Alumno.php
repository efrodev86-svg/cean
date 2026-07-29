<?php

namespace App\Models;

use App\Support\AlumnoEstatus;
use App\Support\AlumnoFicha;
use App\Support\AlumnoTipoIngreso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alumno extends Model
{
    protected $fillable = [
        'matricula',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'grado',
        'grupo',
        'semestre',
        'licenciatura',
        'curp',
        'fecha_nacimiento',
        'ciclo_escolar_id',
        'grupo_id',
        'referencia_pago',
        'email_institucional',
        'email_personal',
        'domicilio',
        'colonia',
        'codigo_postal',
        'estado',
        'municipio',
        'celular',
        'telefono_emergencia',
        'nss',
        'tiene_diagnostico',
        'diagnostico_detalle',
        'tiene_discapacidad',
        'discapacidad_detalle',
        'estado_civil',
        'labora',
        'lugar_trabajo',
        'estatus',
        'tipo_ingreso',
        'entidad_procedencia',
        'ciudad_procedencia',
        'asignatura_adeuda',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'semestre' => 'integer',
            'tiene_diagnostico' => 'boolean',
            'tiene_discapacidad' => 'boolean',
            'labora' => 'boolean',
        ];
    }

    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    public function grupoEscolar(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function nombreFormal(): string
    {
        $partes = array_filter([
            $this->apellido_paterno,
            $this->apellido_materno,
            $this->nombres,
        ]);

        return mb_strtoupper(trim(implode(' ', $partes)));
    }

    public function semestreParImpar(): string
    {
        return $this->semestre % 2 === 0 ? 'PAR' : 'IMPAR';
    }

    /**
     * Identificador semestre + salón para la boleta oficial (ej. "8- A").
     */
    public function semestreGrupo(): string
    {
        return "{$this->semestre}- {$this->grupo}";
    }

    public function resumenAcademico(): string
    {
        return "{$this->semestre}° · Grupo {$this->grupo} · {$this->licenciatura}";
    }

    public function emailAcceso(): ?string
    {
        return AlumnoFicha::resolverEmailAcceso($this->email_institucional, $this->email_personal);
    }

    public function etiquetaEstatus(): string
    {
        return AlumnoEstatus::etiqueta($this->estatus);
    }

    public function etiquetaTipoIngreso(): string
    {
        return AlumnoTipoIngreso::etiqueta($this->tipo_ingreso);
    }

    public function esTraslado(): bool
    {
        return AlumnoTipoIngreso::esTraslado($this->tipo_ingreso);
    }
}
