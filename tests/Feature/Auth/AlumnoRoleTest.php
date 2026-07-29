<?php

namespace Tests\Feature\Auth;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumno_is_redirected_to_alumno_dashboard_on_login(): void
    {
        $user = User::factory()->alumno()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('alumno.dashboard', absolute: false));
    }

    public function test_inactive_alumno_cannot_login(): void
    {
        $user = User::factory()->alumno()->create(['activo' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_alumno_can_access_portal_routes(): void
    {
        $user = User::factory()->alumno()->create();

        $this->actingAs($user)
            ->get(route('alumno.dashboard'))
            ->assertOk()
            ->assertSee('Portal alumno', false);
    }

    public function test_alumno_profile_uses_alumno_portal_layout(): void
    {
        $ciclo = CicloEscolar::query()->create(['nombre' => '2025-2026', 'activo' => true]);

        $alumno = Alumno::query()->create([
            'matricula' => '2025888',
            'nombres' => 'Ana',
            'apellido_paterno' => 'García',
            'apellido_materno' => null,
            'grado' => '2° Semestre',
            'grupo' => 'A',
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
            'fecha_nacimiento' => '2005-03-10',
            'ciclo_escolar_id' => $ciclo->id,
        ]);

        $user = User::factory()->alumno()->create([
            'alumno_id' => $alumno->id,
            'name' => 'Ana García',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Mi perfil', false)
            ->assertSee('Datos académicos', false)
            ->assertSee('2025888', false)
            ->assertSee('Datos de acceso', false)
            ->assertSee('Solo puedes cambiar tu contraseña', false)
            ->assertSee('Contraseña', false)
            ->assertSee('Cerrar sesión', false)
            ->assertDontSee('Información de la cuenta', false)
            ->assertDontSee('Guardar cambios', false)
            ->assertDontSee('Eliminar cuenta', false)
            ->assertDontSeeHtml('aria-label="Navegación administración"');
    }

    public function test_alumno_cannot_update_name_or_email(): void
    {
        $user = User::factory()->alumno()->create([
            'name' => 'Ana García',
            'email' => 'ana.garcia@ensq.edu.mx',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Otro Nombre',
                'email' => 'otro@ensq.edu.mx',
            ])
            ->assertForbidden();

        $user->refresh();

        $this->assertSame('Ana García', $user->name);
        $this->assertSame('ana.garcia@ensq.edu.mx', $user->email);
    }

    public function test_alumno_cannot_access_admin_routes(): void
    {
        $user = User::factory()->alumno()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_alumno_cannot_access_docente_routes(): void
    {
        $user = User::factory()->alumno()->create();

        $this->actingAs($user)
            ->get(route('docente.dashboard'))
            ->assertForbidden();
    }

    public function test_user_can_be_linked_to_alumno_record(): void
    {
        $ciclo = CicloEscolar::query()->create(['nombre' => '2025-2026', 'activo' => true]);

        $alumno = Alumno::query()->create([
            'matricula' => '2025999',
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Martínez',
            'apellido_materno' => null,
            'grado' => '2° Semestre',
            'grupo' => 'A',
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
            'fecha_nacimiento' => '2005-06-15',
            'ciclo_escolar_id' => $ciclo->id,
        ]);

        $user = User::factory()->alumno()->create([
            'alumno_id' => $alumno->id,
            'name' => 'Pedro Martínez',
        ]);

        $this->assertTrue($user->isAlumno());
        $this->assertSame('Alumno', $user->roleLabel());
        $this->assertTrue($user->alumno->is($alumno));
        $this->assertTrue($alumno->user->is($user));
    }
}
