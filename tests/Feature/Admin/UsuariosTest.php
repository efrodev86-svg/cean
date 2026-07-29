<?php

namespace Tests\Feature\Admin;

use App\Models\CicloEscolar;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    private function encargado(Sede $sede): User
    {
        return User::factory()->create([
            'role' => 'encargado',
            'sede_id' => $sede->id,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_puede_crear_encargado_de_sede(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede Norte', 'clave' => 'CCT-N']);

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), [
                'name' => 'Encargada Norte',
                'email' => 'encargada.norte@escuela.test',
                'role' => 'encargado',
                'sede_id' => $sede->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect();

        $usuario = User::query()->where('email', 'encargada.norte@escuela.test')->firstOrFail();
        $this->assertSame($sede->id, $usuario->sede_id);
        $this->assertSame('encargado', $usuario->role);
        $this->assertTrue(Hash::check('password123', $usuario->password));
    }

    public function test_encargado_requiere_sede(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), [
                'name' => 'Sin Sede',
                'email' => 'sinsede@escuela.test',
                'role' => 'encargado',
                'sede_id' => '',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('sede_id');
    }

    public function test_admin_puede_crear_encargado_docente(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede Central', 'clave' => 'CCT-C']);

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), [
                'name' => 'Mtra. Dual Perfil',
                'email' => 'dual@escuela.test',
                'role' => 'encargado-docente',
                'sede_id' => $sede->id,
                'codigo' => 'DOC-DUAL',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect();

        $usuario = User::query()->where('email', 'dual@escuela.test')->firstOrFail();
        $this->assertSame('encargado-docente', $usuario->role);
        $this->assertSame($sede->id, $usuario->sede_id);
        $this->assertTrue($usuario->isEncargado());
        $this->assertTrue($usuario->isDocente());
        $this->assertTrue($usuario->sedes()->where('sedes.id', $sede->id)->exists());
    }

    public function test_encargado_docente_accede_a_control_escolar_y_portal_docente(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede Central', 'clave' => 'CCT-C']);
        $usuario = User::factory()->create([
            'role' => 'encargado-docente',
            'sede_id' => $sede->id,
            'email_verified_at' => now(),
        ]);
        $usuario->sedes()->sync([$sede->id]);

        $this->actingAs($usuario)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($usuario)->get(route('docente.dashboard'))->assertOk();
    }

    public function test_encargado_de_sede_solo_ve_su_sede_en_ciclos(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        CicloEscolar::query()->create(['sede_id' => $sedeA->id, 'nombre' => 'A-2024', 'activo' => true]);
        CicloEscolar::query()->create(['sede_id' => $sedeB->id, 'nombre' => 'B-2024', 'activo' => true]);

        $response = $this->actingAs($this->encargado($sedeA))->get(route('admin.ciclos.index'));

        $response->assertOk();
        $response->assertSee('Sede A');
        $response->assertSee('A-2024');
        $response->assertDontSee('Sede B');
        $response->assertDontSee('B-2024');
    }

    public function test_usuarios_por_defecto_solo_muestra_control_escolar(): void
    {
        $this->admin();
        User::factory()->create(['name' => 'Profe Solo', 'role' => 'docente', 'email_verified_at' => now()]);
        User::factory()->create(['name' => 'Encargada Lista', 'role' => 'encargado', 'sede_id' => Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A'])->id, 'email_verified_at' => now()]);

        $response = $this->actingAs($this->admin())->get(route('admin.usuarios.index'));

        $response->assertOk();
        $response->assertSee('Encargada Lista');
        $response->assertDontSee('Profe Solo');
    }

    public function test_vista_docentes_muestra_solo_docentes(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $docente = User::factory()->create(['name' => 'Profe Vista', 'role' => 'docente', 'email_verified_at' => now()]);
        $docente->sedes()->sync([$sede->id]);
        User::factory()->create(['name' => 'Admin Oculto', 'role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($this->admin())->get(route('admin.usuarios.index', ['vista' => 'docentes']));

        $response->assertOk();
        $response->assertSee('Profe Vista');
        $response->assertDontSee('Admin Oculto');
    }

    public function test_encargado_no_accede_a_sedes_ni_usuarios_ni_materias(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $encargado = $this->encargado($sede);

        $this->actingAs($encargado)->get(route('admin.sedes.index'))->assertForbidden();
        $this->actingAs($encargado)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($encargado)->get(route('admin.materias'))->assertForbidden();
    }

    public function test_encargado_si_accede_a_su_panel_y_calificaciones(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $encargado = $this->encargado($sede);

        $this->actingAs($encargado)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($encargado)->get(route('admin.calificaciones.index'))->assertOk();
    }

    public function test_encargado_no_puede_modificar_ciclo_de_otra_sede(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        $cicloB = CicloEscolar::query()->create(['sede_id' => $sedeB->id, 'nombre' => 'B-2024', 'activo' => true]);

        $this->actingAs($this->encargado($sedeA))
            ->patch(route('admin.ciclos.update', $cicloB), ['nombre' => 'Hackeado', 'activo' => '1'])
            ->assertForbidden();

        $this->assertSame('B-2024', $cicloB->refresh()->nombre);
    }

    public function test_admin_global_ve_todas_las_sedes_en_ciclos(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        CicloEscolar::query()->create(['sede_id' => $sedeA->id, 'nombre' => 'A-2024', 'activo' => true]);
        CicloEscolar::query()->create(['sede_id' => $sedeB->id, 'nombre' => 'B-2024', 'activo' => true]);

        $response = $this->actingAs($this->admin())->get(route('admin.ciclos.index'));

        $response->assertOk();
        $response->assertSee('Sede A');
        $response->assertSee('Sede B');
    }

    public function test_no_puede_eliminar_su_propio_usuario(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.usuarios.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
