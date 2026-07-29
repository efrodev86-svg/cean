<?php

namespace App\Support;

class AlumnoFicha
{
    public static function resolverEmailAcceso(?string $institucional, ?string $personal): ?string
    {
        $institucional = self::normalizarEmail($institucional);
        $personal = self::normalizarEmail($personal);

        return $institucional ?? $personal;
    }

    public static function normalizarEmail(?string $email): ?string
    {
        if (! filled($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public static function atributosDesdeRegistro(array $datos): array
    {
        return [
            'referencia_pago' => filled($datos['referencia_pago'] ?? null) ? $datos['referencia_pago'] : null,
            'email_institucional' => self::normalizarEmail($datos['email_institucional'] ?? null),
            'email_personal' => self::normalizarEmail($datos['email_personal'] ?? null),
            'domicilio' => filled($datos['domicilio'] ?? null) ? $datos['domicilio'] : null,
            'colonia' => filled($datos['colonia'] ?? null) ? $datos['colonia'] : null,
            'codigo_postal' => filled($datos['codigo_postal'] ?? null) ? (string) $datos['codigo_postal'] : null,
            'estado' => filled($datos['estado'] ?? null) ? $datos['estado'] : null,
            'municipio' => filled($datos['municipio'] ?? null) ? $datos['municipio'] : null,
            'celular' => filled($datos['celular'] ?? null) ? (string) $datos['celular'] : null,
            'telefono_emergencia' => filled($datos['telefono_emergencia'] ?? null) ? (string) $datos['telefono_emergencia'] : null,
            'nss' => filled($datos['nss'] ?? null) ? (string) $datos['nss'] : null,
            'tiene_diagnostico' => (bool) ($datos['tiene_diagnostico'] ?? false),
            'diagnostico_detalle' => filled($datos['diagnostico_detalle'] ?? null) ? $datos['diagnostico_detalle'] : null,
            'tiene_discapacidad' => (bool) ($datos['tiene_discapacidad'] ?? false),
            'discapacidad_detalle' => filled($datos['discapacidad_detalle'] ?? null) ? $datos['discapacidad_detalle'] : null,
            'estado_civil' => \App\Support\AlumnoEstadoCivil::normalizar($datos['estado_civil'] ?? null),
            'labora' => (bool) ($datos['labora'] ?? false),
            'lugar_trabajo' => filled($datos['lugar_trabajo'] ?? null) ? $datos['lugar_trabajo'] : null,
            'estatus' => AlumnoEstatus::desdeRegistroImport($datos),
            'tipo_ingreso' => AlumnoTipoIngreso::normalizar($datos['tipo_ingreso'] ?? AlumnoTipoIngreso::NUEVO),
            'entidad_procedencia' => filled($datos['entidad_procedencia'] ?? null) ? $datos['entidad_procedencia'] : null,
            'ciudad_procedencia' => filled($datos['ciudad_procedencia'] ?? null) ? $datos['ciudad_procedencia'] : null,
            'asignatura_adeuda' => filled($datos['asignatura_adeuda'] ?? null) ? $datos['asignatura_adeuda'] : null,
        ];
    }
}
