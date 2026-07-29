<?php

namespace App\Support;

class AlumnoEstadoCivil
{
    public const SOLTERO = 'Soltero(a)';

    public const CASADO = 'Casado(a)';

    public const VIUDO = 'Viudo(a)';

    public const CONCUBINATO = 'Concubinato';

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        return [
            self::SOLTERO => self::SOLTERO,
            self::CASADO => self::CASADO,
            self::VIUDO => self::VIUDO,
            self::CONCUBINATO => self::CONCUBINATO,
        ];
    }

    public static function normalizar(?string $valor): ?string
    {
        if (! filled($valor)) {
            return null;
        }

        $valor = trim($valor);

        if (array_key_exists($valor, self::opciones())) {
            return $valor;
        }

        $clave = mb_strtolower(preg_replace('/\s+/', '', $valor) ?? '');

        return match (true) {
            str_contains($clave, 'soltero') => self::SOLTERO,
            str_contains($clave, 'casado') => self::CASADO,
            str_contains($clave, 'viudo') => self::VIUDO,
            str_contains($clave, 'concubinato'),
            str_contains($clave, 'uniónlibre'),
            str_contains($clave, 'unionlibre') => self::CONCUBINATO,
            default => null,
        };
    }
}
