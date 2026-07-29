<?php

namespace App\Support;

class AlumnoEstatus
{
    public const REGULAR = 'regular';

    public const IRREGULAR = 'irregular';

    public const BAJA_TEMPORAL = 'baja_temporal';

    public const BAJA_DEFINITIVA = 'baja_definitiva';

    public const EGRESADO = 'egresado';

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        return [
            self::REGULAR => 'Regular',
            self::IRREGULAR => 'Irregular',
            self::BAJA_TEMPORAL => 'Baja temporal',
            self::BAJA_DEFINITIVA => 'Baja definitiva',
            self::EGRESADO => 'Egresado',
        ];
    }

    public static function etiqueta(?string $estatus): string
    {
        return self::opciones()[$estatus] ?? 'Regular';
    }

    public static function normalizar(?string $estatus): string
    {
        $estatus = strtolower(trim((string) $estatus));

        return array_key_exists($estatus, self::opciones()) ? $estatus : self::REGULAR;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public static function desdeRegistroImport(array $datos): string
    {
        if (filled($datos['estatus'] ?? null)) {
            return self::normalizar($datos['estatus']);
        }

        if ((bool) ($datos['es_irregular'] ?? false)) {
            return self::IRREGULAR;
        }

        return self::REGULAR;
    }

    public static function bloqueaAccesoPortal(?string $estatus): bool
    {
        return in_array($estatus, [self::BAJA_TEMPORAL, self::BAJA_DEFINITIVA], true);
    }
}
