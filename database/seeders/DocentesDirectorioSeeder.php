<?php

namespace Database\Seeders;

use App\Services\DirectorioDocenteImportService;
use Illuminate\Database\Seeder;

class DocentesDirectorioSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/directorio-docentes-2526.json');

        if (! is_file($path)) {
            $this->command?->warn("No se encontró {$path}; omitiendo importación de docentes.");

            return;
        }

        $resultado = app(DirectorioDocenteImportService::class)->importFromJson($path);

        $this->command?->info(sprintf(
            'Directorio docentes 25-26: %d nuevos, %d actualizados.',
            $resultado['importados'],
            $resultado['actualizados'],
        ));
    }
}
