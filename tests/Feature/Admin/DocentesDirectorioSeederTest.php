<?php

namespace Tests\Feature\Admin;

use App\Models\Sede;
use App\Models\User;
use App\Services\DirectorioDocenteImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocentesDirectorioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_directorio_docentes_desde_json(): void
    {
        Sede::query()->create(['nombre' => 'Sede Central', 'clave' => '22DNL0001P']);

        $path = database_path('data/directorio-docentes-2526.json');
        $this->assertFileExists($path);

        $resultado = app(DirectorioDocenteImportService::class)->importFromJson($path);

        $this->assertGreaterThan(0, $resultado['importados'] + $resultado['actualizados']);
        $this->assertGreaterThan(20, User::query()->docentes()->count());

        $docente = User::query()->where('email', 'ncancino@ensq.edu.mx')->first();
        $this->assertNotNull($docente);
        $this->assertSame('Nayely', $docente->nombre);
        $this->assertSame('4423055423', $docente->celular);
    }
}
