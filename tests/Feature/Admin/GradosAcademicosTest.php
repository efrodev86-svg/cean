<?php

namespace Tests\Feature\Admin;

use App\Models\GradoAcademico;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradosAcademicosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    public function test_admin_puede_registrar_grado_academico(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.docentes.grados-academicos.store'), [
                'abreviatura' => 'Dr.',
            ])
            ->assertRedirect(route('admin.docentes.grados-academicos.index'));

        $this->assertDatabaseHas('grados_academicos', [
            'abreviatura' => 'Dr.',
        ]);
    }

    public function test_docente_muestra_nombre_con_grado(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $grado = GradoAcademico::query()->create([
            'abreviatura' => 'Mtro.',
        ]);

        $docente = User::factory()->create([
            'nombre' => 'Ana',
            'primer_apellido' => 'López',
            'name' => 'Ana López',
            'role' => 'docente',
            'grado_academico_id' => $grado->id,
            'email_verified_at' => now(),
        ]);
        $docente->sedes()->sync([$sede->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.docentes.index'))
            ->assertOk()
            ->assertSee('Mtro. Ana López');
    }

    public function test_admin_puede_actualizar_grado_academico(): void
    {
        $grado = GradoAcademico::query()->create(['abreviatura' => 'Lic.']);

        $this->actingAs($this->admin())
            ->patch(route('admin.docentes.grados-academicos.update', $grado), [
                'grado_edit_id' => $grado->id,
                'abreviatura' => 'Lic.',
                'activo' => '0',
            ])
            ->assertRedirect(route('admin.docentes.grados-academicos.index'));

        $this->assertDatabaseHas('grados_academicos', [
            'id' => $grado->id,
            'abreviatura' => 'Lic.',
            'activo' => false,
        ]);
    }

    public function test_pagina_grados_muestra_formulario_y_listado(): void
    {
        GradoAcademico::query()->create(['abreviatura' => 'Dr.']);

        $this->actingAs($this->admin())
            ->get(route('admin.docentes.grados-academicos.index'))
            ->assertOk()
            ->assertSee('Nueva abreviatura')
            ->assertSee('Dr.')
            ->assertDontSee('Nuevo grado');
    }

    public function test_no_elimina_grado_con_docentes_asignados(): void
    {
        $grado = GradoAcademico::query()->create([
            'abreviatura' => 'Dr.',
        ]);

        User::factory()->create([
            'role' => 'docente',
            'grado_academico_id' => $grado->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.docentes.grados-academicos.destroy', $grado))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('grados_academicos', ['id' => $grado->id]);
    }
}
