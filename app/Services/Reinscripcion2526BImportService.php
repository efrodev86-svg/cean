<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Sede;
use App\Models\User;
use App\Support\AlumnoFicha;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Reinscripcion2526BImportService
{
    private const SEDE_CLAVE = '22DNL0001P';

    private const CICLO_NOMBRE = '2025-2026';

    private const GRUPO_LETRA = 'A';

    /**
     * Correcciones puntuales de fecha cuando la CURP tiene errores de captura.
     *
     * @var array<string, string>
     */
    private const FECHA_NACIMIENTO_OVERRIDES = [
        'RERDO11227MQTYJNA0' => '2004-11-27',
    ];

    /**
     * @return array{
     *     grupos: int,
     *     alumnos: int,
     *     usuarios: int,
     *     sin_correo: int,
     *     advertencias: list<string>
     * }
     */
    public function importFromJsonFile(string $path): array
    {
        $registros = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($registros)) {
            throw new \InvalidArgumentException('El archivo de reinscripción no contiene un arreglo válido.');
        }

        $advertencias = [];

        return DB::transaction(function () use ($registros, &$advertencias) {
            $sede = $this->resolverSede();
            $ciclo = $this->resolverCiclo($sede);
            $grupos = $this->resolverGrupos($sede, $ciclo, $registros);

            $alumnosImportados = 0;
            $usuariosImportados = 0;
            $sinCorreo = 0;

            foreach ($registros as $registro) {
                $email = $this->resolverEmail($registro, $advertencias, $sinCorreo);
                $fechaNacimiento = $this->resolverFechaNacimiento($registro, $advertencias);

                if ($fechaNacimiento === null) {
                    $advertencias[] = "Omitido por fecha de nacimiento inválida: {$registro['apellido_paterno']} {$registro['nombres']}";

                    continue;
                }

                $grupo = $grupos[$this->grupoKey($registro)] ?? null;

                if ($grupo === null) {
                    $advertencias[] = "Grupo no encontrado para {$registro['apellido_paterno']} {$registro['nombres']}";

                    continue;
                }

                $nombreCompleto = User::nombreCompletoDesdePartes([
                    'nombre' => $registro['nombres'],
                    'primer_apellido' => $registro['apellido_paterno'],
                    'segundo_apellido' => $registro['apellido_materno'] ?? null,
                ]);

                $alumno = Alumno::query()->updateOrCreate(
                    ['matricula' => $registro['matricula']],
                    [
                        'nombres' => $registro['nombres'],
                        'apellido_paterno' => $registro['apellido_paterno'],
                        'apellido_materno' => $registro['apellido_materno'],
                        'grado' => "{$registro['semestre']}° Semestre",
                        'grupo' => self::GRUPO_LETRA,
                        'semestre' => $registro['semestre'],
                        'licenciatura' => $registro['licenciatura'],
                        'curp' => $registro['curp'],
                        'fecha_nacimiento' => $fechaNacimiento,
                        'ciclo_escolar_id' => $ciclo->id,
                        'grupo_id' => $grupo->id,
                        ...AlumnoFicha::atributosDesdeRegistro($registro),
                    ]
                );

                $alumnosImportados++;

                $atributosUsuario = [
                    'email' => $email,
                    'name' => $nombreCompleto,
                    'nombre' => $registro['nombres'],
                    'primer_apellido' => $registro['apellido_paterno'],
                    'segundo_apellido' => $registro['apellido_materno'],
                    'curp' => $registro['curp'],
                    'celular' => $registro['celular'] ?? null,
                    'role' => User::ROLE_ALUMNO,
                    'activo' => true,
                    'sede_id' => $sede->id,
                    'email_verified_at' => now(),
                ];

                $usuario = User::query()->where('alumno_id', $alumno->id)->first();

                if ($usuario) {
                    $usuario->update($atributosUsuario);
                } else {
                    $usuario = User::query()->create([
                        ...$atributosUsuario,
                        'alumno_id' => $alumno->id,
                        'password' => Hash::make($registro['matricula']),
                    ]);
                }

                $usuariosImportados++;
            }

            return [
                'grupos' => count($grupos),
                'alumnos' => $alumnosImportados,
                'usuarios' => $usuariosImportados,
                'sin_correo' => $sinCorreo,
                'advertencias' => $advertencias,
            ];
        });
    }

    private function resolverSede(): Sede
    {
        return Sede::query()->firstOrCreate(
            ['clave' => self::SEDE_CLAVE],
            [
                'nombre' => 'Sede Central',
                'escuela' => 'ESCUELA NORMAL SUPERIOR DE QUERÉTARO',
                'director' => 'MTRO. ROBERTO COMPEÁN MARTÍNEZ',
                'ciudad' => 'SANTIAGO DE QUERÉTARO, QRO.',
                'activa' => true,
            ]
        );
    }

    private function resolverCiclo(Sede $sede): CicloEscolar
    {
        CicloEscolar::query()
            ->where('sede_id', $sede->id)
            ->update(['activo' => false]);

        $ciclo = CicloEscolar::query()->updateOrCreate(
            ['sede_id' => $sede->id, 'nombre' => self::CICLO_NOMBRE],
            [
                'activo' => true,
                'fecha_emision_boletas' => '2026-07-15',
            ]
        );

        $ciclo->periodos()->updateOrCreate(
            ['clave' => 'A'],
            [
                'nombre' => 'Periodo A · Agosto 2025 – Enero 2026',
                'fecha_inicio' => '2025-08-18',
                'fecha_cierre' => '2026-01-30',
                'fecha_entrega_calificaciones' => '2026-01-16',
                'fecha_consulta_boletas' => '2026-01-23',
                'activo' => false,
            ]
        );

        $ciclo->periodos()->updateOrCreate(
            ['clave' => 'B'],
            [
                'nombre' => 'Periodo B · Febrero – Julio 2026',
                'fecha_inicio' => '2026-02-02',
                'fecha_cierre' => '2026-07-10',
                'fecha_entrega_calificaciones' => '2026-06-26',
                'fecha_consulta_boletas' => '2026-07-03',
                'activo' => true,
            ]
        );

        return $ciclo;
    }

    /**
     * @param  list<array<string, mixed>>  $registros
     * @return array<string, Grupo>
     */
    private function resolverGrupos(Sede $sede, CicloEscolar $ciclo, array $registros): array
    {
        $combinaciones = collect($registros)
            ->map(fn (array $registro) => $this->grupoKey($registro))
            ->unique()
            ->values();

        $grupos = [];

        foreach ($combinaciones as $clave) {
            [$semestre, $licenciatura] = explode('|', $clave, 2);

            $grupo = Grupo::query()->updateOrCreate(
                [
                    'ciclo_escolar_id' => $ciclo->id,
                    'semestre' => (int) $semestre,
                    'letra' => self::GRUPO_LETRA,
                    'licenciatura' => $licenciatura,
                ],
                [
                    'sede_id' => $sede->id,
                    'nombre' => "{$semestre}°-".self::GRUPO_LETRA." · {$licenciatura}",
                ]
            );

            $grupos[$clave] = $grupo;
        }

        return $grupos;
    }

    /**
     * @param  array<string, mixed>  $registro
     */
    private function grupoKey(array $registro): string
    {
        return "{$registro['semestre']}|{$registro['licenciatura']}";
    }

    /**
     * @param  array<string, mixed>  $registro
     * @param  list<string>  $advertencias
     */
    private function resolverEmail(array $registro, array &$advertencias, int &$sinCorreo): string
    {
        $email = AlumnoFicha::resolverEmailAcceso(
            $registro['email_institucional'] ?? null,
            $registro['email_personal'] ?? null,
        ) ?? AlumnoFicha::normalizarEmail($registro['email'] ?? null);

        if ($email !== null) {
            return $email;
        }

        $sinCorreo++;
        $generado = Str::lower($registro['matricula']).'@alumnos.ensq.edu.mx';
        $advertencias[] = "Correo generado para {$registro['apellido_paterno']} {$registro['nombres']}: {$generado}";

        return $generado;
    }

    /**
     * @param  array<string, mixed>  $registro
     * @param  list<string>  $advertencias
     */
    private function resolverFechaNacimiento(array $registro, array &$advertencias): ?string
    {
        $curp = strtoupper((string) ($registro['curp'] ?? ''));

        if ($curp !== '' && isset(self::FECHA_NACIMIENTO_OVERRIDES[$curp])) {
            return self::FECHA_NACIMIENTO_OVERRIDES[$curp];
        }

        if (! empty($registro['fecha_nacimiento'])) {
            return $registro['fecha_nacimiento'];
        }

        if (strlen($curp) < 10) {
            return null;
        }

        $anio = substr($curp, 4, 2);
        $mes = substr($curp, 6, 2);
        $dia = substr($curp, 8, 2);

        if (! ctype_digit($anio.$mes.$dia)) {
            return null;
        }

        $yy = (int) $anio;
        $year = $yy > 30 ? 1900 + $yy : 2000 + $yy;

        return sprintf('%04d-%02d-%02d', $year, (int) $mes, (int) $dia);
    }

    /**
     * Completa o actualiza solo los campos de ficha en alumnos ya importados.
     *
     * @return array{
     *     actualizados: int,
     *     no_encontrados: list<string>,
     *     sin_correo: int,
     *     advertencias: list<string>
     * }
     */
    public function actualizarFichasFromJsonFile(string $path): array
    {
        $registros = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($registros)) {
            throw new \InvalidArgumentException('El archivo de reinscripción no contiene un arreglo válido.');
        }

        $advertencias = [];
        $noEncontrados = [];
        $actualizados = 0;
        $sinCorreo = 0;

        return DB::transaction(function () use ($registros, &$advertencias, &$noEncontrados, &$actualizados, &$sinCorreo) {
            foreach ($registros as $registro) {
                $matricula = (string) ($registro['matricula'] ?? '');

                if ($matricula === '') {
                    continue;
                }

                $alumno = Alumno::query()->where('matricula', $matricula)->first();

                if ($alumno === null) {
                    $noEncontrados[] = $matricula;

                    continue;
                }

                $fechaNacimiento = $this->resolverFechaNacimiento($registro, $advertencias);
                $email = $this->resolverEmail($registro, $advertencias, $sinCorreo);

                $alumno->update([
                    'curp' => $registro['curp'] ?? $alumno->curp,
                    'fecha_nacimiento' => $fechaNacimiento ?? $alumno->fecha_nacimiento,
                    ...AlumnoFicha::atributosDesdeRegistro($registro),
                ]);

                $usuario = User::query()->where('alumno_id', $alumno->id)->first();

                if ($usuario !== null) {
                    $usuario->update([
                        'email' => $email,
                        'curp' => $registro['curp'] ?? $usuario->curp,
                        'celular' => filled($registro['celular'] ?? null) ? (string) $registro['celular'] : $usuario->celular,
                    ]);
                }

                $actualizados++;
            }

            return [
                'actualizados' => $actualizados,
                'no_encontrados' => $noEncontrados,
                'sin_correo' => $sinCorreo,
                'advertencias' => $advertencias,
            ];
        });
    }
}
