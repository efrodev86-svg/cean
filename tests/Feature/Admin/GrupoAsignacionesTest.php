<?php

namespace Tests\Feature\Admin;

use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\GrupoMateriaDocente;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Sede;
use App\Models\User;
use App\Services\PlanEstudioImportService;
use App\Services\Reinscripcion2526BImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoAsignacionesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    private function docenteEnSede(Sede $sede): User
    {
        $docente = User::factory()->create([
            'role' => 'docente',
            'sede_id' => null,
            'activo' => true,
            'email_verified_at' => now(),
            'nombre' => 'Carlos',
            'primer_apellido' => 'Hernández',
            'segundo_apellido' => 'Ruiz',
        ]);
        $docente->sedes()->sync([$sede->id]);

        return $docente;
    }

    public function test_admin_puede_asignar_docente_a_materia_del_grupo(): void
    {
        foreach (config('planes_estudio', []) as $plan) {
            app(PlanEstudioImportService::class)->importFromJsonFile(database_path('data/'.$plan['archivo']));
        }
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
        ])->firstOrFail();

        $materia = $grupo->materiasDelPlan()->first();
        $this->assertNotNull($materia);

        $docente = $this->docenteEnSede($grupo->sede);

        $asignaciones = $grupo->materiasDelPlan()->values()->map(fn (Materia $m, int $i) => [
            'materia_id' => $m->id,
            'docente_id' => $m->id === $materia->id ? $docente->id : null,
        ])->all();

        $this->actingAs($this->admin())
            ->put(route('admin.grupos.asignaciones.update', $grupo), [
                'asignaciones' => $asignaciones,
            ])
            ->assertRedirect(route('admin.grupos.asignaciones.edit', $grupo))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('grupo_materia_docente', [
            'grupo_id' => $grupo->id,
            'materia_id' => $materia->id,
            'docente_id' => $docente->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.docentes.edit', $docente))
            ->assertOk()
            ->assertSee('Historial de asignaturas', false)
            ->assertSee($materia->nombre, false)
            ->assertSee($grupo->etiqueta(), false)
            ->assertSee('2025-2026', false);
    }

    public function test_quitar_docente_elimina_asignacion(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Central', 'clave' => '22DNL0001P']);
        $ciclo = CicloEscolar::query()->create([
            'sede_id' => $sede->id,
            'nombre' => '2025-2026',
            'activo' => true,
        ]);
        $licenciatura = Licenciatura::query()->create([
            'nombre_corto' => 'TELESECUNDARIA',
            'nombre' => 'Telesecundaria',
            'plan_nombre' => '2022',
            'anio_plan' => 2022,
            'activa' => true,
        ]);
        $materia = Materia::query()->create([
            'licenciatura_id' => $licenciatura->id,
            'nombre' => 'Didáctica',
            'clave' => 'ST22999',
            'semestre' => 2,
            'orden' => 1,
        ]);
        $grupo = Grupo::query()->create([
            'sede_id' => $sede->id,
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'letra' => 'A',
            'licenciatura' => 'TELESECUNDARIA',
            'nombre' => '2°-A · TELESECUNDARIA',
        ]);
        $docente = $this->docenteEnSede($sede);

        GrupoMateriaDocente::query()->create([
            'grupo_id' => $grupo->id,
            'materia_id' => $materia->id,
            'docente_id' => $docente->id,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.grupos.asignaciones.update', $grupo), [
                'asignaciones' => [
                    ['materia_id' => $materia->id, 'docente_id' => null],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('grupo_materia_docente', [
            'grupo_id' => $grupo->id,
            'materia_id' => $materia->id,
        ]);
    }
}
