<?php

namespace Tests\Feature\Admin;

use App\Models\EstudioCursado;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudiosCursadosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    private function docente(): User
    {
        return User::factory()->create([
            'role' => 'docente',
            'sede_id' => null,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_registra_estudio_cursado_del_docente(): void
    {
        $docente = $this->docente();

        $this->actingAs($this->admin())
            ->post(route('admin.docentes.estudios-cursados.store', $docente), [
                'descripcion' => 'Maestría en Educación',
                'documento_probatorio' => 'Cédula profesional 12345',
                'fecha' => '2018-06-15',
            ])
            ->assertRedirect(route('admin.docentes.edit', $docente))
            ->assertSessionHas('estudios_modal', true);

        $this->assertDatabaseHas('estudios_cursados', [
            'user_id' => $docente->id,
            'descripcion' => 'Maestría en Educación',
            'documento_probatorio' => 'Cédula profesional 12345',
        ]);
    }

    public function test_editar_docente_muestra_seccion_estudios_cursados(): void
    {
        $docente = $this->docente();

        EstudioCursado::query()->create([
            'user_id' => $docente->id,
            'descripcion' => 'Licenciatura en Pedagogía',
            'documento_probatorio' => 'Título',
            'fecha' => '2010-03-01',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.docentes.edit', $docente))
            ->assertOk()
            ->assertSee('Estudios cursados')
            ->assertSee('Gestionar estudios')
            ->assertSee('1 estudio(s) registrado(s)');
    }

    public function test_encargado_puede_gestionar_estudios_de_docente_de_su_sede(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $encargado = User::factory()->create(['role' => 'encargado', 'sede_id' => $sede->id, 'email_verified_at' => now()]);
        $docente = $this->docente();
        $docente->sedes()->sync([$sede->id]);

        $this->actingAs($encargado)
            ->post(route('admin.docentes.estudios-cursados.store', $docente), [
                'descripcion' => 'Doctorado',
                'fecha' => '2020-01-01',
            ])
            ->assertRedirect(route('admin.docentes.edit', $docente));

        $this->assertDatabaseHas('estudios_cursados', [
            'user_id' => $docente->id,
            'descripcion' => 'Doctorado',
        ]);
    }

    public function test_admin_elimina_estudio_cursado(): void
    {
        $docente = $this->docente();
        $estudio = EstudioCursado::query()->create([
            'user_id' => $docente->id,
            'descripcion' => 'Curso SEP',
            'fecha' => '2019-05-10',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.docentes.estudios-cursados.destroy', [$docente, $estudio]))
            ->assertRedirect(route('admin.docentes.edit', $docente));

        $this->assertDatabaseMissing('estudios_cursados', ['id' => $estudio->id]);
    }
}
