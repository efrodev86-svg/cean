<?php

namespace App\Services;

use App\Models\GradoAcademico;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DirectorioDocenteImportService
{
    /**
     * @return array{importados: int, actualizados: int, grados: int}
     */
    public function importFromJson(string $path): array
    {
        $registros = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($registros)) {
            throw new \InvalidArgumentException('El archivo de directorio no contiene un arreglo válido.');
        }

        $sedes = $this->sedesPorClave();
        $importados = 0;
        $actualizados = 0;

        foreach ($registros as $registro) {
            $gradoId = $this->resolverGradoId($registro['grado_abreviatura'] ?? null);

            $nombreCompleto = User::nombreCompletoDesdePartes([
                'nombre' => $registro['nombre'],
                'primer_apellido' => $registro['primer_apellido'],
                'segundo_apellido' => $registro['segundo_apellido'] ?? null,
            ]);

            $atributos = [
                'nombre' => $registro['nombre'],
                'primer_apellido' => $registro['primer_apellido'],
                'segundo_apellido' => $registro['segundo_apellido'] ?? null,
                'name' => $nombreCompleto,
                'curp' => strtoupper((string) $registro['curp']),
                'grado_academico_id' => $gradoId,
                'tipo_contratacion' => $registro['tipo_contratacion'] ?? 'base',
                'nombre_plaza' => $registro['nombre_plaza'] ?? null,
                'celular' => (string) ($registro['celular'] ?? ''),
                'role' => 'docente',
                'activo' => true,
                'sede_id' => null,
                'email_verified_at' => now(),
            ];

            $docente = User::query()->where('email', $registro['email'])->first();

            if ($docente) {
                if (! $docente->isDocente()) {
                    continue;
                }

                $docente->fill($atributos);

                if (! $docente->password) {
                    $docente->password = Hash::make(Str::password(16));
                }

                $docente->save();
                $actualizados++;
            } else {
                $docente = User::query()->create([
                    ...$atributos,
                    'email' => $registro['email'],
                    'password' => Hash::make(Str::password(16)),
                ]);
                $importados++;
            }

            $sedeIds = collect($registro['sedes'] ?? ['central'])
                ->map(fn (string $clave) => $sedes[$clave] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($sedeIds !== []) {
                $docente->sedes()->syncWithoutDetaching($sedeIds);
            }
        }

        return [
            'importados' => $importados,
            'actualizados' => $actualizados,
            'grados' => GradoAcademico::query()->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function sedesPorClave(): array
    {
        $central = Sede::query()->firstOrCreate(
            ['clave' => '22DNL0001P'],
            [
                'nombre' => 'Sede Central',
                'escuela' => 'ESCUELA NORMAL SUPERIOR DE QUERÉTARO',
                'director' => 'MTRO. ROBERTO COMPEÁN MARTÍNEZ',
                'ciudad' => 'SANTIAGO DE QUERÉTARO, QRO.',
                'activa' => true,
            ]
        );

        $toliman = Sede::query()->firstOrCreate(
            ['clave' => 'ENSQ-TOLIMAN'],
            [
                'nombre' => 'Unidad Tolimán',
                'escuela' => 'ESCUELA NORMAL SUPERIOR DE QUERÉTARO',
                'ciudad' => 'TOLIMÁN, QRO.',
                'activa' => true,
            ]
        );

        return [
            'central' => $central->id,
            'toliman' => $toliman->id,
        ];
    }

    private function resolverGradoId(?string $abreviatura): ?int
    {
        if ($abreviatura === null || $abreviatura === '') {
            return null;
        }

        $normalizada = rtrim(trim($abreviatura), '.').'.';

        $grado = GradoAcademico::query()->firstOrCreate(
            ['abreviatura' => $normalizada],
            ['activo' => true]
        );

        return $grado->id;
    }
}
