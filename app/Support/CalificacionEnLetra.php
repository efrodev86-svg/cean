<?php

namespace App\Support;

class CalificacionEnLetra
{
    /** @var array<int, string> */
    private const UNIDADES = [
        0 => 'CERO',
        1 => 'UNO',
        2 => 'DOS',
        3 => 'TRES',
        4 => 'CUATRO',
        5 => 'CINCO',
        6 => 'SEIS',
        7 => 'SIETE',
        8 => 'OCHO',
        9 => 'NUEVE',
        10 => 'DIEZ',
    ];

    public static function entero(float|int|string $calificacion): string
    {
        $valor = (int) round((float) $calificacion);

        return self::UNIDADES[max(0, min(10, $valor))];
    }

    public static function decimal(float|int|string $calificacion): string
    {
        $valor = round((float) $calificacion, 1);
        $entero = (int) floor($valor);
        $decima = (int) round(($valor - $entero) * 10);

        $parteEntera = self::entero($entero);

        if ($decima === 0) {
            return "{$parteEntera} PUNTO CERO";
        }

        return "{$parteEntera} PUNTO ".self::entero($decima);
    }
}
