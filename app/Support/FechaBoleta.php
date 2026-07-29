<?php

namespace App\Support;

use Carbon\CarbonInterface;

class FechaBoleta
{
    /** @var array<int, string> */
    private const MESES = [
        1 => 'ENERO',
        2 => 'FEBRERO',
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    public static function destacada(CarbonInterface $fecha, ?string $ciudad = null): string
    {
        $dia = $fecha->day;
        $mes = self::MESES[$fecha->month];
        $anio = $fecha->year;

        $ciudad = $ciudad ?: config('boleta.ciudad');

        return "{$ciudad}, A {$dia} DE {$mes} {$anio}";
    }
}
