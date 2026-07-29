<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoletaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumno_puede_consultar_su_boleta(): void
    {
        $ciclo = CicloEscolar::query()->create([
            'nombre' => '2023-2024',
            'activo' => true,
            'fecha_emision_boletas' => '2024-05-29',
        ]);

        $alumno = Alumno::query()->create([
            'matricula' => '201559590000',
            'nombres' => 'JORGE LUIS',
            'apellido_paterno' => 'BENITEZ',
            'apellido_materno' => 'SALAZAR',
            'grado' => '8° Semestre',
            'grupo' => 'A',
            'semestre' => 8,
            'licenciatura' => 'TELESECUNDARIA',
            'fecha_nacimiento' => '2000-01-15',
            'ciclo_escolar_id' => $ciclo->id,
        ]);

        $materia = Materia::query()->create([
            'nombre' => 'APRENDIZAJE EN EL SERVICIO',
        ]);

        Calificacion::query()->create([
            'alumno_id' => $alumno->id,
            'materia_id' => $materia->id,
            'semestre' => 8,
            'calificacion' => 9.0,
            'asistencia_porcentaje' => 95,
        ]);

        $response = $this->post(route('boleta.consultar'), [
            'matricula' => $alumno->matricula,
            'fecha_nacimiento' => '2000-01-15',
        ]);

        $response->assertOk();
        $response->assertSee('BENITEZ SALAZAR JORGE LUIS');
        $response->assertSee('8- A');
        $response->assertSee('semestre PAR');
        $response->assertSee('Promedio :');
        $response->assertSee('APRENDIZAJE EN EL SERVICIO');
        $response->assertSee('NUEVE PUNTO CERO');
        $response->assertSee('29 DE MAYO 2024');
    }
}
