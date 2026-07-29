<?php

namespace App\Services;

use App\Models\Licenciatura;
use App\Models\Materia;
use Illuminate\Support\Facades\DB;

class PlanEstudioImportService
{
    /**
     * @return array{licenciatura: Licenciatura, materias: int, creadas: int, actualizadas: int}
     */
    public function importFromJsonFile(string $path): array
    {
        if (! is_file($path)) {
            throw new \InvalidArgumentException("No se encontró el archivo del plan: {$path}");
        }

        /** @var array{licenciatura: array<string, mixed>, materias: list<array<string, mixed>>} $payload */
        $payload = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        return DB::transaction(function () use ($payload) {
            $datos = $payload['licenciatura'];

            $licenciatura = Licenciatura::query()->updateOrCreate(
                ['nombre_corto' => $datos['nombre_corto']],
                [
                    'nombre' => $datos['nombre'],
                    'plan_nombre' => $datos['plan_nombre'] ?? null,
                    'anio_plan' => $datos['anio_plan'] ?? null,
                    'activa' => true,
                ]
            );

            $creadas = 0;
            $actualizadas = 0;

            foreach ($payload['materias'] as $fila) {
                $existente = Materia::query()
                    ->where('licenciatura_id', $licenciatura->id)
                    ->where('clave', $fila['clave'])
                    ->exists();

                Materia::query()->updateOrCreate(
                    [
                        'licenciatura_id' => $licenciatura->id,
                        'clave' => $fila['clave'],
                    ],
                    [
                        'nombre' => $fila['nombre'],
                        'semestre' => $fila['semestre'],
                        'orden' => $fila['orden'],
                        'creditos' => $fila['creditos'],
                        'horas_semana' => $fila['horas_semana'],
                        'horas_semestre' => $fila['horas_semestre'],
                        'grado' => $fila['semestre'].'° Semestre',
                    ]
                );

                if ($existente) {
                    $actualizadas++;
                } else {
                    $creadas++;
                }
            }

            return [
                'licenciatura' => $licenciatura,
                'materias' => count($payload['materias']),
                'creadas' => $creadas,
                'actualizadas' => $actualizadas,
            ];
        });
    }
}
