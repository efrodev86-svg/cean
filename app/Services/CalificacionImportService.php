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
    public function importFromCsv(UploadedFile $archivo, int $semestre, CicloEscolar $ciclo): array
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

            [$matricula, $materiaNombre, $calificacion, $asistencia] = array_pad($fila, 4, null);
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

            if ($asistencia !== null && $asistencia !== '' && (! is_numeric($asistencia) || $asistencia < 0 || $asistencia > 100)) {
                $errores[] = "Línea {$linea}: porcentaje de asistencia inválido (0-100).";

                continue;
            }

            $materia = Materia::query()->firstOrCreate(
                ['nombre' => $materiaNombre],
                ['grado' => (string) $alumno->semestre]
            );

            DB::transaction(function () use ($alumno, $materia, $semestre, $calificacion, $asistencia, &$importadas): void {
                Calificacion::query()->updateOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'materia_id' => $materia->id,
                        'semestre' => $semestre,
                    ],
                    [
                        'calificacion' => round((float) $calificacion, 1),
                        'asistencia_porcentaje' => is_numeric($asistencia) ? (int) $asistencia : 100,
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
