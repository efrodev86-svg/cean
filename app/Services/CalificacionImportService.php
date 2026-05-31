<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CalificacionImportService
{
    /**
     * @return array{importadas: int, errores: list<string>}
     */
    public function importFromCsv(UploadedFile $archivo, int $bimestre, CicloEscolar $ciclo): array
    {
        $importadas = 0;
        $errores = [];
        $handle = fopen($archivo->getRealPath(), 'r');

        if ($handle === false) {
            return [
                'importadas' => 0,
                'errores' => ['No se pudo leer el archivo CSV.'],
            ];
        }

        $encabezado = fgetcsv($handle);

        if ($encabezado === false) {
            fclose($handle);

            return [
                'importadas' => 0,
                'errores' => ['El archivo CSV está vacío.'],
            ];
        }

        $linea = 1;

        while (($fila = fgetcsv($handle)) !== false) {
            $linea++;

            if ($this->filaVacia($fila)) {
                continue;
            }

            if (count($fila) < 3) {
                $errores[] = "Línea {$linea}: se requieren al menos matrícula, materia y calificación.";

                continue;
            }

            [$matricula, $materiaNombre, $calificacion, $faltas] = array_pad($fila, 4, null);
            $matricula = trim((string) $matricula);
            $materiaNombre = trim((string) $materiaNombre);

            $alumno = Alumno::query()
                ->where('matricula', $matricula)
                ->where('ciclo_escolar_id', $ciclo->id)
                ->first();

            if (! $alumno) {
                $errores[] = "Línea {$linea}: no se encontró al alumno con matrícula {$matricula}.";

                continue;
            }

            if (! is_numeric($calificacion) || $calificacion < 0 || $calificacion > 10) {
                $errores[] = "Línea {$linea}: calificación inválida (debe ser entre 0 y 10).";

                continue;
            }

            $materia = Materia::query()->firstOrCreate(
                ['nombre' => $materiaNombre],
                ['grado' => $alumno->grado]
            );

            DB::transaction(function () use ($alumno, $materia, $bimestre, $calificacion, $faltas, &$importadas): void {
                Calificacion::query()->updateOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'materia_id' => $materia->id,
                        'bimestre' => $bimestre,
                    ],
                    [
                        'calificacion' => round((float) $calificacion, 1),
                        'faltas' => is_numeric($faltas) ? (int) $faltas : 0,
                    ]
                );

                $importadas++;
            });
        }

        fclose($handle);

        return compact('importadas', 'errores');
    }

    /**
     * @param  list<string|null>  $fila
     */
    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (trim((string) $valor) !== '') {
                return false;
            }
        }

        return true;
    }
}
