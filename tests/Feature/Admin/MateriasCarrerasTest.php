<?php

namespace Tests\Feature\Admin;

use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\User;
use App\Services\PlanEstudioImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriasCarrerasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_ver_modulo_materias_y_carreras(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.materias'));

        $response->assertOk();
        $response->assertSee('Materias y Carreras');
        $response->assertSee('Licenciaturas');
        $response->assertSee('Registrar', false);
        $response->assertSee('Nueva licenciatura');
        $response->assertDontSee('Importar Telesecundaria');
    }

    public function test_planes_oficiales_se_cargan_desde_datos_del_sistema(): void
    {
        $importService = app(PlanEstudioImportService::class);

        foreach (config('planes_estudio', []) as $plan) {
            $importService->importFromJsonFile(database_path('data/'.$plan['archivo']));
        }

        $telesecundaria = Licenciatura::query()->where('nombre_corto', 'TELESECUNDARIA')->first();
        $espanol = Licenciatura::query()->where('nombre_corto', 'ESPAÑOL')->first();

        $this->assertNotNull($telesecundaria);
        $this->assertNotNull($espanol);
        $this->assertSame(54, Materia::query()->where('licenciatura_id', $telesecundaria->id)->count());
        $this->assertSame(52, Materia::query()->where('licenciatura_id', $espanol->id)->count());
    }

    public function test_materia_sin_orden_se_agrega_al_final_del_semestre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $licenciatura = Licenciatura::query()->create([
            'nombre_corto' => 'TEST',
            'nombre' => 'Licenciatura de prueba',
            'activa' => true,
        ]);

        Materia::query()->create([
            'licenciatura_id' => $licenciatura->id,
            'clave' => 'T001',
            'nombre' => 'Materia uno',
            'semestre' => 1,
            'orden' => 3,
            'creditos' => 4.5,
            'grado' => '1° Semestre',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.materias.store'), [
            'licenciatura_id' => $licenciatura->id,
            'clave' => 'T002',
            'nombre' => 'Materia nueva',
            'semestre' => 1,
            'creditos' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materias', [
            'clave' => 'T002',
            'orden' => 4,
        ]);
    }

    public function test_admin_puede_guardar_licenciatura_sin_clave_dgp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $licenciatura = Licenciatura::query()->create([
            'nombre_corto' => 'TEST',
            'nombre' => 'Licenciatura de prueba',
            'clave_dgp' => 'DGP1234567',
            'activa' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.licenciaturas.update', $licenciatura), [
            'nombre_corto' => 'TEST',
            'clave_dgp' => '',
            'nombre' => 'Licenciatura de prueba',
            'activa' => 1,
        ]);

        $response->assertRedirect(route('admin.materias', ['licenciatura' => $licenciatura->id]));
        $this->assertDatabaseHas('licenciaturas', [
            'id' => $licenciatura->id,
            'clave_dgp' => null,
        ]);
    }

    public function test_admin_puede_actualizar_clave_dgp_de_licenciatura(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $licenciatura = Licenciatura::query()->create([
            'nombre_corto' => 'TEST',
            'nombre' => 'Licenciatura de prueba',
            'activa' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.licenciaturas.update', $licenciatura), [
            'nombre_corto' => 'TEST',
            'clave_dgp' => 'DGP1234567',
            'nombre' => 'Licenciatura de prueba',
            'activa' => 1,
        ]);

        $response->assertRedirect(route('admin.materias', ['licenciatura' => $licenciatura->id]));
        $this->assertDatabaseHas('licenciaturas', [
            'id' => $licenciatura->id,
            'clave_dgp' => 'DGP1234567',
        ]);
    }

    public function test_admin_puede_reordenar_materias_de_un_semestre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $licenciatura = Licenciatura::query()->create([
            'nombre_corto' => 'TEST',
            'nombre' => 'Licenciatura de prueba',
            'activa' => true,
        ]);

        $materiaA = Materia::query()->create([
            'licenciatura_id' => $licenciatura->id,
            'clave' => 'T001',
            'nombre' => 'Materia A',
            'semestre' => 1,
            'orden' => 1,
            'creditos' => 4,
            'grado' => '1° Semestre',
        ]);

        $materiaB = Materia::query()->create([
            'licenciatura_id' => $licenciatura->id,
            'clave' => 'T002',
            'nombre' => 'Materia B',
            'semestre' => 1,
            'orden' => 2,
            'creditos' => 4,
            'grado' => '1° Semestre',
        ]);

        $response = $this->actingAs($admin)->patchJson(route('admin.materias.reordenar'), [
            'licenciatura_id' => $licenciatura->id,
            'semestre' => 1,
            'materias' => [$materiaB->id, $materiaA->id],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('materias', ['id' => $materiaB->id, 'orden' => 1]);
        $this->assertDatabaseHas('materias', ['id' => $materiaA->id, 'orden' => 2]);
    }

    public function test_docente_no_puede_acceder_al_modulo(): void
    {
        $docente = User::factory()->create(['role' => 'docente']);

        $response = $this->actingAs($docente)->get(route('admin.materias'));

        $response->assertForbidden();
    }
}
