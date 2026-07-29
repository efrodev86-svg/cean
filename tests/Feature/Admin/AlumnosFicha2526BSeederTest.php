<?php

namespace Tests\Feature\Admin;

use App\Models\Alumno;
use App\Models\User;
use App\Services\Reinscripcion2526BImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnosFicha2526BSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualizar_fichas_completa_datos_extendidos_sin_reimportar(): void
    {
        $import = app(Reinscripcion2526BImportService::class);
        $import->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $alumno = Alumno::query()->where('matricula', '252206940000')->firstOrFail();
        $alumno->update([
            'domicilio' => null,
            'celular' => null,
            'email_institucional' => null,
            'nss' => null,
        ]);

        $resultado = $import->actualizarFichasFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $this->assertSame(155, $resultado['actualizados']);
        $this->assertSame([], $resultado['no_encontrados']);

        $alumno->refresh();
        $this->assertSame('brendateresa.aguillon@ensq.edu.mx', $alumno->email_institucional);
        $this->assertNotNull($alumno->domicilio);
        $this->assertNotNull($alumno->celular);
        $this->assertNotNull($alumno->nss);

        $usuario = User::query()->where('alumno_id', $alumno->id)->firstOrFail();
        $this->assertSame('brendateresa.aguillon@ensq.edu.mx', $usuario->email);
    }
}
