<?php

namespace Tests\Feature\Admin;

use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Sede;
use App\Models\User;
use App\Services\Reinscripcion2526BImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GruposTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    public function test_admin_puede_ver_listado_de_grupos_importados(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $this->actingAs($this->admin())
            ->get(route('admin.grupos'))
            ->assertOk()
            ->assertSee('Grupos escolares', false)
            ->assertSee('2A', false)
            ->assertSee('ESPANOL', false)
            ->assertSee('TELESECUNDARIA', false)
            ->assertSee('2025-2026', false);
    }

    public function test_listado_muestra_conteo_de_alumnos_por_grupo(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'licenciatura' => 'ESPANOL',
        ])->firstOrFail();

        $this->assertSame(21, $grupo->alumnos()->count());

        $this->actingAs($this->admin())
            ->get(route('admin.grupos', ['ciclo' => $ciclo->id]))
            ->assertOk()
            ->assertSee('Ver alumnos', false);
    }

    public function test_encargado_solo_ve_grupos_de_su_sede(): void
    {
        $sedeCentral = Sede::query()->where('clave', '22DNL0001P')->first()
            ?? Sede::query()->create(['nombre' => 'Central', 'clave' => '22DNL0001P']);

        $sedeOtra = Sede::query()->create(['nombre' => 'Otra', 'clave' => 'CCT-OTRA']);

        $cicloCentral = CicloEscolar::query()->create([
            'sede_id' => $sedeCentral->id,
            'nombre' => '2025-2026',
            'activo' => true,
        ]);

        $cicloOtra = CicloEscolar::query()->create([
            'sede_id' => $sedeOtra->id,
            'nombre' => '2025-2026',
            'activo' => true,
        ]);

        Grupo::query()->create([
            'sede_id' => $sedeCentral->id,
            'ciclo_escolar_id' => $cicloCentral->id,
            'semestre' => 2,
            'letra' => 'A',
            'licenciatura' => 'ESPANOL',
            'nombre' => '2°-A · ESPANOL',
        ]);

        Grupo::query()->create([
            'sede_id' => $sedeOtra->id,
            'ciclo_escolar_id' => $cicloOtra->id,
            'semestre' => 2,
            'letra' => 'A',
            'licenciatura' => 'ESPANOL',
            'nombre' => '2°-A · ESPANOL',
        ]);

        $encargado = User::factory()->create([
            'role' => 'encargado',
            'sede_id' => $sedeCentral->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($encargado)->get(route('admin.grupos'));

        $response->assertOk();
        $this->assertSame(1, Grupo::query()->where('sede_id', $sedeCentral->id)->count());
    }

    public function test_admin_puede_crear_editar_y_eliminar_grupo(): void
    {
        $sede = Sede::query()->firstOrCreate(
            ['clave' => '22DNL0001P'],
            ['nombre' => 'Sede Central']
        );

        $ciclo = CicloEscolar::query()->create([
            'sede_id' => $sede->id,
            'nombre' => '2026-2027',
            'activo' => true,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.grupos.create', ['ciclo' => $ciclo->id]))
            ->assertOk()
            ->assertSee('Registrar grupo', false);

        $this->actingAs($this->admin())
            ->post(route('admin.grupos.store'), [
                'ciclo_escolar_id' => $ciclo->id,
                'semestre' => 2,
                'letra' => 'B',
                'licenciatura' => 'ESPANOL',
            ])
            ->assertRedirect(route('admin.grupos', ['sede' => $sede->id, 'ciclo' => $ciclo->id]))
            ->assertSessionHas('success');

        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'letra' => 'B',
            'licenciatura' => 'ESPANOL',
        ])->firstOrFail();

        $this->assertSame('2°-B · ESPANOL', $grupo->nombre);

        $this->actingAs($this->admin())
            ->patch(route('admin.grupos.update', $grupo), [
                'ciclo_escolar_id' => $ciclo->id,
                'semestre' => 2,
                'letra' => 'C',
                'licenciatura' => 'ESPANOL',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('C', $grupo->fresh()->letra);

        $this->actingAs($this->admin())
            ->delete(route('admin.grupos.destroy', $grupo))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('grupos', ['id' => $grupo->id]);
    }

    public function test_no_puede_eliminar_grupo_con_alumnos(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $grupo = Grupo::query()->where('semestre', 2)->where('licenciatura', 'ESPANOL')->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('admin.grupos.destroy', $grupo))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('grupos', ['id' => $grupo->id]);
    }
}
