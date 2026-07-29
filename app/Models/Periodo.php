<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Periodo extends Model
{
    protected $table = 'periodos';

    protected $fillable = [
        'ciclo_escolar_id',
        'clave',
        'nombre',
        'fecha_inicio',
        'fecha_cierre',
        'fecha_entrega_calificaciones',
        'fecha_consulta_boletas',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_cierre' => 'date',
            'fecha_entrega_calificaciones' => 'date',
            'fecha_consulta_boletas' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    /**
     * Periodo A = semestres impares (Ago–Ene) · Periodo B = semestres pares (Feb–Jul).
     */
    public function paridad(): string
    {
        return $this->clave === 'A' ? 'IMPAR' : 'PAR';
    }

    public function aplicaASemestre(int $semestre): bool
    {
        $esImpar = $semestre % 2 === 1;

        return $esImpar === ($this->clave === 'A');
    }

    /**
     * La captura/carga de calificaciones está habilitada dentro de la ventana
     * fecha_inicio → fecha_entrega_calificaciones.
     */
    public function enVentanaCaptura(?Carbon $referencia = null): bool
    {
        $hoy = ($referencia ?? Carbon::now())->startOfDay();

        if ($this->fecha_inicio && $hoy->lt($this->fecha_inicio->startOfDay())) {
            return false;
        }

        if ($this->fecha_entrega_calificaciones && $hoy->gt($this->fecha_entrega_calificaciones->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * La boleta queda disponible para el alumno a partir de fecha_consulta_boletas.
     */
    public function boletasDisponibles(?Carbon $referencia = null): bool
    {
        if (! $this->fecha_consulta_boletas) {
            return true;
        }

        $hoy = ($referencia ?? Carbon::now())->startOfDay();

        return $hoy->gte($this->fecha_consulta_boletas->startOfDay());
    }

    public function etiqueta(): string
    {
        return "Periodo {$this->clave}";
    }
}
