<?php

namespace Database\Seeders;

use App\Services\Reinscripcion2526BImportService;
use Illuminate\Database\Seeder;

class Reinscripcion2526BSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/reinscripcion-2526b.json');

        if (! is_file($path)) {
            $this->command?->warn("No se encontró {$path}; omitiendo importación de reinscripción 25-26B.");

            return;
        }

        $resultado = app(Reinscripcion2526BImportService::class)->importFromJsonFile($path);

        $this->command?->info(sprintf(
            'Reinscripción 25-26B: %d grupos, %d alumnos, %d accesos (%d con correo generado).',
            $resultado['grupos'],
            $resultado['alumnos'],
            $resultado['usuarios'],
            $resultado['sin_correo'],
        ));

        foreach ($resultado['advertencias'] as $advertencia) {
            $this->command?->warn($advertencia);
        }
    }
}
