<?php

namespace Tests\Feature\Admin;

use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocentesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    private function encargado(Sede $sede): User
    {
        return User::factory()->create(['role' => 'encargado', 'sede_id' => $sede->id, 'email_verified_at' => now()]);
    }

    /**
     * @param  array<int, int>  $sedes
     * @param  array<string, mixed>  $attrs
     */
    private function docente(array $sedes = [], array $attrs = []): User
    {
        $docente = User::factory()->create(array_merge([
            'role' => 'docente',
            'sede_id' => null,
            'email_verified_at' => now(),
        ], $attrs));

        $docente->sedes()->sync($sedes);

        return $docente;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function datosDocente(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'primer_apellido' => 'Ruiz',
            'segundo_apellido' => 'López',
            'email' => 'ana.ruiz@escuela.test',
            'curp' => 'RULO900215MQTPNS08',
            'tipo_contratacion' => 'base',
            'clave_plaza' => 'PLZ-001',
            'nombre_plaza' => 'Docente de asignatura',
            'celular' => '4421234567',
        ], $overrides);
    }

    public function test_admin_ve_formulario_de_nuevo_docente(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.docentes.create'))
            ->assertOk()
            ->assertSee('Registrar docente')
            ->assertSee('Primer apellido')
            ->assertSee('Tipo de contratación');
    }

    public function test_admin_ve_formulario_de_editar_docente(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $docente = $this->docente([$sede->id], $this->datosDocente([
            'email' => 'editar@escuela.test',
            'name' => 'Ana Ruiz López',
        ]));

        $this->actingAs($this->admin())
            ->get(route('admin.docentes.edit', $docente))
            ->assertOk()
            ->assertSee('Editar docente')
            ->assertSee('editar@escuela.test');
    }

    public function test_admin_crea_docente_en_varias_sedes(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);

        $this->actingAs($this->admin())
            ->post(route('admin.docentes.store'), [
                ...$this->datosDocente(),
                'sedes' => [$sedeA->id, $sedeB->id],
            ])
            ->assertRedirect();

        $docente = User::query()->where('email', 'ana.ruiz@escuela.test')->firstOrFail();
        $this->assertSame('docente', $docente->role);
        $this->assertSame('Ana', $docente->nombre);
        $this->assertSame('Ruiz', $docente->primer_apellido);
        $this->assertSame('Ana Ruiz López', $docente->name);
        $this->assertEqualsCanonicalizing(
            [$sedeA->id, $sedeB->id],
            $docente->sedes()->pluck('sedes.id')->all()
        );
    }

    public function test_admin_requiere_al_menos_una_sede(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.docentes.store'), $this->datosDocente([
                'email' => 'sinsede.doc@escuela.test',
            ]))
            ->assertSessionHasErrors('sedes');
    }

    public function test_correo_existente_reutiliza_docente_y_agrega_sede(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        $docente = $this->docente([$sedeA->id], $this->datosDocente(['email' => 'compartido@escuela.test']));

        $this->actingAs($this->encargado($sedeB))
            ->post(route('admin.docentes.store'), $this->datosDocente([
                'email' => 'compartido@escuela.test',
                'curp' => 'COMP850101HDFRRR01',
            ]))
            ->assertRedirect();

        $this->assertSame(1, User::query()->where('email', 'compartido@escuela.test')->count());
        $this->assertEqualsCanonicalizing(
            [$sedeA->id, $sedeB->id],
            $docente->fresh()->sedes()->pluck('sedes.id')->all()
        );
    }

    public function test_encargado_solo_ve_docentes_de_su_sede(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        $this->docente([$sedeA->id], ['name' => 'Profe Alfa', 'email' => 'alfa@escuela.test']);
        $this->docente([$sedeB->id], ['name' => 'Profe Beta', 'email' => 'beta@escuela.test']);

        $response = $this->actingAs($this->encargado($sedeA))->get(route('admin.docentes.index'));

        $response->assertOk();
        $response->assertSee('Profe Alfa');
        $response->assertDontSee('Profe Beta');
    }

    public function test_encargado_al_eliminar_solo_quita_de_su_sede(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        $docente = $this->docente([$sedeA->id, $sedeB->id], ['email' => 'multi@escuela.test']);

        $this->actingAs($this->encargado($sedeA))
            ->delete(route('admin.docentes.destroy', $docente))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $docente->id]);
        $this->assertEqualsCanonicalizing(
            [$sedeB->id],
            $docente->fresh()->sedes()->pluck('sedes.id')->all()
        );
    }

    public function test_encargado_no_gestiona_docente_de_otra_sede(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $sedeB = Sede::query()->create(['nombre' => 'Sede B', 'clave' => 'CCT-B']);
        $docente = $this->docente([$sedeB->id], $this->datosDocente(['email' => 'ajeno@escuela.test']));

        $this->actingAs($this->encargado($sedeA))
            ->get(route('admin.docentes.edit', $docente))
            ->assertForbidden();

        $this->actingAs($this->encargado($sedeA))
            ->patch(route('admin.docentes.update', $docente), $this->datosDocente([
                'nombre' => 'Hackeado',
                'email' => 'ajeno@escuela.test',
            ]))
            ->assertForbidden();

        $this->assertNotSame('Hackeado', $docente->fresh()->nombre);
    }

    public function test_admin_puede_crear_docente_sin_datos_de_plaza(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);

        $this->actingAs($this->admin())
            ->post(route('admin.docentes.store'), [
                ...$this->datosDocente([
                    'email' => 'sinplaza@escuela.test',
                    'curp' => 'SIPL900101HDFRRR01',
                ]),
                'clave_plaza' => '',
                'nombre_plaza' => '',
                'sedes' => [$sede->id],
            ])
            ->assertRedirect();

        $docente = User::query()->where('email', 'sinplaza@escuela.test')->firstOrFail();
        $this->assertNull($docente->clave_plaza);
        $this->assertNull($docente->nombre_plaza);
    }

    public function test_admin_puede_deshabilitar_docente(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $docente = $this->docente([$sede->id], $this->datosDocente(['email' => 'inactivo@escuela.test']));

        $this->actingAs($this->admin())
            ->patch(route('admin.docentes.update', $docente), [
                ...$this->datosDocente(['email' => 'inactivo@escuela.test']),
                'sedes' => [$sede->id],
                'activo' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse($docente->fresh()->activo);
    }

    public function test_docente_inactivo_no_puede_iniciar_sesion(): void
    {
        $docente = User::factory()->create([
            'role' => 'docente',
            'activo' => false,
            'email' => 'bloqueado@escuela.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->post(route('login'), [
            'email' => $docente->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_elimina_docente_por_completo(): void
    {
        $sedeA = Sede::query()->create(['nombre' => 'Sede A', 'clave' => 'CCT-A']);
        $docente = $this->docente([$sedeA->id], ['email' => 'borrar@escuela.test']);

        $this->actingAs($this->admin())
            ->delete(route('admin.docentes.destroy', $docente))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $docente->id]);
    }
}
