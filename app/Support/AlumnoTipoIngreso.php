<?php

namespace App\Support;

class AlumnoTipoIngreso
{
    public const NUEVO = 'nuevo';

    public const TRASLADO = 'traslado';

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        return [
            self::NUEVO => 'Ingreso nuevo',
            self::TRASLADO => 'Traslado',
        ];
    }

    public static function etiqueta(?string $tipo): string
    {
        return self::opciones()[$tipo] ?? 'Ingreso nuevo';
    }

    public static function normalizar(?string $tipo): string
    {
        $tipo = strtolower(trim((string) $tipo));

        return array_key_exists($tipo, self::opciones()) ? $tipo : self::NUEVO;
    }

    public static function esTraslado(?string $tipo): bool
    {
        return self::normalizar($tipo) === self::TRASLADO;
    }
}
