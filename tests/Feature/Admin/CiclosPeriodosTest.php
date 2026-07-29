<?php

namespace Tests\Feature\Admin;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiclosPeriodosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_ve_la_pantalla_de_ciclos_y_periodos(): void
    {
        $ciclo = CicloEscolar::query()->create(['nombre' => '2024-2025', 'activo' => true]);
        $ciclo->crearPeriodosPredeterminados();

        $response = $this->actingAs($this->admin())->get(route('admin.ciclos.index'));

        $response->assertOk();
        $response->assertSee('Ciclos y periodos');
        $response->assertSee('2024-2025');
        $response->assertSee('Periodo A');
        $response->assertSee('Periodo B');
        $response->assertSee('Consulta de boletas');
    }

    public function test_crear_ciclo_genera_dos_periodos(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede Centro', 'clave' => 'CCT-001']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.ciclos.store'), ['sede_id' => $sede->id, 'nombre' => '2024-2025']);

        $response->assertRedirect(route('admin.ciclos.index'));

        $ciclo = CicloEscolar::query()->where('nombre', '2024-2025')->firstOrFail();

        $this->assertSame($sede->id, $ciclo->sede_id);
        $this->assertEqualsCanonicalizing(
            ['A', 'B'],
            $ciclo->periodos()->pluck('clave')->all()
        );
    }

    public function test_activar_un_ciclo_desactiva_los_demas_de_la_misma_sede(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede Centro', 'clave' => 'CCT-001']);
        $anterior = CicloEscolar::query()->create(['sede_id' => $sede->id, 'nombre' => '2023-2024', 'activo' => true]);
        $nuevo = CicloEscolar::query()->create(['sede_id' => $sede->id, 'nombre' => '2024-2025', 'activo' => false]);

        $this->actingAs($this->admin())
            ->patch(route('admin.ciclos.update', $nuevo), ['nombre' => '2024-2025', 'activo' => '1'])
            ->assertRedirect(route('admin.ciclos.index'));

        $this->assertFalse($anterior->refresh()->activo);
        $this->assertTrue($nuevo->refresh()->activo);
    }

    public function test_activar_ciclo_no_afecta_otras_sedes(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        $cicloA = CicloEscolar::query()->create(['sede_id' => $sedeA->id, 'nombre' => '2024-2025', 'activo' => true]);
        $cicloB = CicloEscolar::query()->create(['sede_id' => $sedeB->id, 'nombre' => '2024-2025', 'activo' => false]);

        $this->actingAs($this->admin())
            ->patch(route('admin.ciclos.update', $cicloB), ['nombre' => '2024-2025', 'activo' => '1'])
            ->assertRedirect(route('admin.ciclos.index'));

        $this->assertTrue($cicloA->refresh()->activo, 'El ciclo de la otra sede debe seguir activo.');
        $this->assertTrue($cicloB->refresh()->activo);
    }

    public function test_actualizar_fechas_de_periodo(): void
    {
        $ciclo = CicloEscolar::query()->create(['nombre' => '2024-2025', 'activo' => true]);
        $ciclo->crearPeriodosPredeterminados();
        $periodo = $ciclo->periodos()->where('clave', 'B')->firstOrFail();

        $this->actingAs($this->admin())
            ->patch(route('admin.periodos.update', $periodo), [
                'nombre' => 'Periodo B · Feb–Jul 2025',
                'fecha_inicio' => '2025-02-03',
                'fecha_cierre' => '2025-07-11',
                'fecha_entrega_calificaciones' => '2025-05-23',
                'fecha_consulta_boletas' => '2025-05-30',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.ciclos.index'));

        $periodo->refresh();

        $this->assertSame('2025-05-30', $periodo->fecha_consulta_boletas->format('Y-m-d'));
        $this->assertTrue($periodo->activo);
    }

    public function test_boleta_bloqueada_antes_de_fecha_de_consulta(): void
    {
        $ciclo = CicloEscolar::query()->create([
            'nombre' => '2024-2025',
            'activo' => true,
            'fecha_emision_boletas' => '2025-05-30',
        ]);

        Periodo::query()->create([
            'ciclo_escolar_id' => $ciclo->id,
            'clave' => 'B',
            'nombre' => 'Periodo B',
            'fecha_consulta_boletas' => now()->addMonth()->format('Y-m-d'),
            'activo' => true,
        ]);

        $alumno = Alumno::query()->create([
            'matricula' => '2025001',
            'nombres' => 'Ana',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'grado' => '2° Semestre',
            'grupo' => 'A',
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
            'fecha_nacimiento' => '2005-01-01',
            'ciclo_escolar_id' => $ciclo->id,
        ]);

        $materia = Materia::query()->create(['nombre' => 'DIDÁCTICA']);
        Calificacion::query()->create([
            'alumno_id' => $alumno->id,
            'materia_id' => $materia->id,
            'semestre' => 2,
            'calificacion' => 9.0,
            'asistencia_porcentaje' => 95,
        ]);

        $response = $this->post(route('boleta.consultar'), [
            'matricula' => '2025001',
            'fecha_nacimiento' => '2005-01-01',
        ]);

        $response->assertOk();
        $response->assertSee('aún no está disponible');
        $response->assertDontSee('DIDÁCTICA');
    }
}
