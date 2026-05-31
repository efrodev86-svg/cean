<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoletaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumno_puede_consultar_su_boleta(): void
    {
        $ciclo = CicloEscolar::query()->create([
            'nombre' => '2025-2026',
            'activo' => true,
        ]);

        $alumno = Alumno::query()->create([
            'matricula' => '2025999',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Sánchez',
            'grado' => '1° Secundaria',
            'grupo' => 'A',
            'fecha_nacimiento' => '2005-03-10',
            'ciclo_escolar_id' => $ciclo->id,
        ]);

        $response = $this->post(route('boleta.consultar'), [
            'matricula' => $alumno->matricula,
            'fecha_nacimiento' => '2005-03-10',
        ]);

        $response->assertOk();
        $response->assertSee('Juan Pérez Sánchez');
    }
}
