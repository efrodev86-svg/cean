<?php

namespace App\Support;

class ReferenciaPago
{
    /** @var array<int, int> */
    private const PONDERACIONES = [4, 8, 3];

    /** @var array<string, int> */
    private const VALORES = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
        '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5,
        'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10,
        'K' => 11, 'L' => 12, 'M' => 13, 'N' => 14, 'O' => 15,
        'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19, 'T' => 20,
        'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25,
        'Z' => 26,
    ];

    /**
     * Genera la referencia bancaria a partir de la CURP (10 primeros caracteres + dígito verificador).
     */
    public static function desdeCurp(?string $curp): ?string
    {
        if (! filled($curp)) {
            return null;
        }

        $base = strtoupper(substr(preg_replace('/\s+/', '', $curp) ?? '', 0, 10));

        if (strlen($base) < 10) {
            return null;
        }

        return self::conDigitoVerificador($base);
    }

    public static function conDigitoVerificador(string $referencia): string
    {
        $referencia = strtoupper(trim($referencia));
        $invertida = strrev($referencia);
        $suma = 0;
        $indicePonderacion = 0;

        foreach (str_split($invertida) as $posicion => $char) {
            if ($posicion % 3 === 0) {
                $indicePonderacion = 0;
            }

            $valor = self::VALORES[$char] ?? 0;
            $producto = $valor * self::PONDERACIONES[$indicePonderacion];
            $suma += $producto >= 10 ? self::sumaDigitos($producto) : $producto;
            $indicePonderacion++;
        }

        $residuo = $suma % 10;
        $digito = 10 - $residuo;

        if ($digito > 9) {
            $digito = 0;
        }

        return $referencia.$digito;
    }

    private static function sumaDigitos(int $valor): int
    {
        $suma = array_sum(str_split((string) $valor));

        return $suma >= 10 ? self::sumaDigitos($suma) : $suma;
    }
}
