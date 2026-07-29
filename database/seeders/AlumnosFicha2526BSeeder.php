<?php

namespace Database\Seeders;

use App\Services\Reinscripcion2526BImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;

class AlumnosFicha2526BSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/reinscripcion-2526b.json');
        $excelPath = env('REINSCRIPCION_2526B_EXCEL');

        if (! is_file($jsonPath) && filled($excelPath) && is_file($excelPath)) {
            $this->exportarJsonDesdeExcel($excelPath, $jsonPath);
        }

        if (! is_file($jsonPath)) {
            $this->command?->warn("No se encontró {$jsonPath}.");
            $this->command?->warn('Define REINSCRIPCION_2526B_EXCEL en .env con la ruta al Excel o ejecuta:');
            $this->command?->warn('  python3 scripts/export-reinscripcion-2526b.py "/ruta/al/archivo.xlsx"');

            return;
        }

        $resultado = app(Reinscripcion2526BImportService::class)->actualizarFichasFromJsonFile($jsonPath);

        $this->command?->info(sprintf(
            'Fichas 25-26B: %d alumnos actualizados, %d no encontrados en BD.',
            $resultado['actualizados'],
            count($resultado['no_encontrados']),
        ));

        if ($resultado['sin_correo'] > 0) {
            $this->command?->warn("{$resultado['sin_correo']} alumno(s) sin correo válido (se generó correo de acceso).");
        }

        foreach ($resultado['no_encontrados'] as $matricula) {
            $this->command?->warn("Alumno no encontrado: {$matricula}");
        }

        foreach ($resultado['advertencias'] as $advertencia) {
            $this->command?->warn($advertencia);
        }
    }

    private function exportarJsonDesdeExcel(string $excelPath, string $jsonPath): void
    {
        $script = base_path('scripts/export-reinscripcion-2526b.py');

        if (! is_file($script)) {
            $this->command?->warn("No se encontró {$script}; no se pudo exportar el Excel.");

            return;
        }

        $this->command?->info("Exportando JSON desde {$excelPath}...");

        $result = Process::run(['python3', $script, $excelPath, $jsonPath]);

        if (! $result->successful()) {
            $this->command?->error($result->errorOutput());

            return;
        }

        $this->command?->info(trim($result->output()));
    }
}
