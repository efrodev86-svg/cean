<?php

namespace Tests\Feature\Admin;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Sede;
use App\Models\User;
use App\Services\Reinscripcion2526BImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'sede_id' => null, 'email_verified_at' => now()]);
    }

    public function test_admin_puede_ver_listado_de_alumnos_importados(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $this->actingAs($this->admin())
            ->get(route('admin.alumnos'))
            ->assertOk()
            ->assertSee('Catálogo de alumnos', false)
            ->assertSee('252206940000', false)
            ->assertSee('AGUILLÓN', false)
            ->assertSee('2025-2026', false);
    }

    public function test_listado_filtra_por_grupo(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 8,
            'licenciatura' => 'ESPANOL',
        ])->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('admin.alumnos', ['ciclo' => $ciclo->id, 'grupo' => $grupo->id]))
            ->assertOk()
            ->assertSee('222200360000', false)
            ->assertDontSee('252206940000', false);
    }

    public function test_encargado_solo_ve_alumnos_de_su_sede(): void
    {
        $sedeCentral = Sede::query()->where('clave', '22DNL0001P')->first()
            ?? Sede::query()->create(['nombre' => 'Central', 'clave' => '22DNL0001P']);

        $sedeOtra = Sede::query()->create(['nombre' => 'Otra', 'clave' => 'CCT-OTRA']);

        $cicloCentral = CicloEscolar::query()->create([
            'sede_id' => $sedeCentral->id,
            'nombre' => '2025-2026',
            'activo' => true,
        ]);

        CicloEscolar::query()->create([
            'sede_id' => $sedeOtra->id,
            'nombre' => '2025-2026',
            'activo' => true,
        ]);

        Alumno::query()->create([
            'matricula' => '9999001',
            'nombres' => 'Ana',
            'apellido_paterno' => 'Local',
            'apellido_materno' => null,
            'grado' => '2° Semestre',
            'grupo' => 'A',
            'semestre' => 2,
            'licenciatura' => 'ESPANOL',
            'fecha_nacimiento' => '2005-01-01',
            'ciclo_escolar_id' => $cicloCentral->id,
        ]);

        Alumno::query()->create([
            'matricula' => '9999002',
            'nombres' => 'Otro',
            'apellido_paterno' => 'Sede',
            'apellido_materno' => null,
            'grado' => '2° Semestre',
            'grupo' => 'A',
            'semestre' => 2,
            'licenciatura' => 'ESPANOL',
            'fecha_nacimiento' => '2005-01-01',
            'ciclo_escolar_id' => CicloEscolar::query()->where('sede_id', $sedeOtra->id)->firstOrFail()->id,
        ]);

        $encargado = User::factory()->create([
            'role' => 'encargado',
            'sede_id' => $sedeCentral->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($encargado)
            ->get(route('admin.alumnos'))
            ->assertOk()
            ->assertSee('9999001', false)
            ->assertDontSee('9999002', false);
    }

    public function test_admin_puede_crear_editar_y_eliminar_alumno(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
        ])->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('admin.alumnos.create', ['ciclo' => $ciclo->id, 'grupo' => $grupo->id]))
            ->assertOk()
            ->assertSee('Registrar alumno', false)
            ->assertSee('Datos académicos', false);

        $this->actingAs($this->admin())
            ->get(route('admin.alumnos.edit', ['alumno' => Alumno::query()->whereNotNull('grupo_id')->firstOrFail(), 'tab' => 'academicos']))
            ->assertOk()
            ->assertSee('Datos académicos', false)
            ->assertSee('Grupo escolar', false);

        $this->actingAs($this->admin())
            ->post(route('admin.alumnos.store'), [
                'grupo_id' => $grupo->id,
                'matricula' => '299900100001',
                'curp' => 'AANP060315HQTRLRA1',
                'nombres' => 'Prueba',
                'apellido_paterno' => 'Alumno',
                'apellido_materno' => 'Nuevo',
                'fecha_nacimiento' => '2006-03-15',
                'email_institucional' => 'prueba.alumno@ensq.edu.mx',
                'celular' => '4420000000',
                'estatus' => 'regular',
                'tipo_ingreso' => 'nuevo',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.alumnos', ['sede' => $grupo->sede_id, 'ciclo' => $ciclo->id, 'grupo' => $grupo->id]))
            ->assertSessionHas('success');

        $alumno = Alumno::query()->where('matricula', '299900100001')->firstOrFail();
        $this->assertSame($grupo->id, $alumno->grupo_id);
        $this->assertSame('AANP0603156', $alumno->referencia_pago);
        $this->assertTrue($alumno->user?->isAlumno());

        $this->actingAs($this->admin())
            ->patch(route('admin.alumnos.update', $alumno), [
                'grupo_id' => $grupo->id,
                'matricula' => '299900100001',
                'curp' => 'AANP060315HQTRLRA1',
                'nombres' => 'Prueba Editada',
                'apellido_paterno' => 'Alumno',
                'apellido_materno' => 'Nuevo',
                'fecha_nacimiento' => '2006-03-15',
                'email_institucional' => 'prueba.alumno@ensq.edu.mx',
                'estatus' => 'regular',
                'tipo_ingreso' => 'nuevo',
                'activo' => '1',
                'tab' => 'academicos',
            ])
            ->assertRedirect(route('admin.alumnos.edit', ['alumno' => $alumno, 'tab' => 'academicos']))
            ->assertSessionHas('success');

        $this->assertSame('Prueba Editada', $alumno->fresh()->nombres);

        $userId = $alumno->user->id;

        $this->actingAs($this->admin())
            ->delete(route('admin.alumnos.destroy', $alumno))
            ->assertRedirect(route('admin.alumnos', ['sede' => $grupo->sede_id, 'ciclo' => $ciclo->id, 'grupo' => $grupo->id]))
            ->assertSessionHas('success');

        $alumno->refresh();
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id]);
        $this->assertDatabaseHas('users', ['id' => $userId]);
        $this->assertSame('baja_definitiva', $alumno->estatus);
        $this->assertFalse($alumno->user->fresh()->activo);
    }

    public function test_celular_y_telefono_emergencia_no_pueden_ser_iguales(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
        ])->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.alumnos.store'), [
                'grupo_id' => $grupo->id,
                'matricula' => '299900300003',
                'curp' => 'TELF060315HQTRLRA3',
                'nombres' => 'Telefono',
                'apellido_paterno' => 'Duplicado',
                'apellido_materno' => null,
                'fecha_nacimiento' => '2006-03-15',
                'email_institucional' => 'telefono.duplicado@ensq.edu.mx',
                'celular' => '4421234567',
                'telefono_emergencia' => '442-123-4567',
                'estatus' => 'regular',
                'tipo_ingreso' => 'nuevo',
                'activo' => '1',
            ])
            ->assertSessionHasErrors(['telefono_emergencia']);

        $this->assertNull(Alumno::query()->where('curp', 'TELF060315HQTRLRA3')->first());
    }

    public function test_traslado_requiere_entidad_y_ciudad(): void
    {
        app(\App\Services\Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
        ])->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.alumnos.store'), [
                'grupo_id' => $grupo->id,
                'matricula' => '299900200002',
                'curp' => 'TAPP060315HQTRLRA2',
                'nombres' => 'Traslado',
                'apellido_paterno' => 'Prueba',
                'apellido_materno' => null,
                'fecha_nacimiento' => '2006-03-15',
                'email_institucional' => 'traslado.alumno@ensq.edu.mx',
                'estatus' => 'regular',
                'tipo_ingreso' => 'traslado',
                'activo' => '1',
            ])
            ->assertSessionHasErrors(['entidad_procedencia', 'ciudad_procedencia']);

        $this->actingAs($this->admin())
            ->post(route('admin.alumnos.store'), [
                'grupo_id' => $grupo->id,
                'matricula' => '299900200002',
                'curp' => 'TAPP060315HQTRLRA2',
                'nombres' => 'Traslado',
                'apellido_paterno' => 'Prueba',
                'apellido_materno' => null,
                'fecha_nacimiento' => '2006-03-15',
                'email_institucional' => 'traslado.alumno@ensq.edu.mx',
                'estatus' => 'regular',
                'tipo_ingreso' => 'traslado',
                'entidad_procedencia' => 'Escuela Normal de Guanajuato',
                'ciudad_procedencia' => 'León, Gto.',
                'activo' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $alumno = Alumno::query()->where('matricula', '299900200002')->firstOrFail();
        $this->assertTrue($alumno->esTraslado());
        $this->assertSame('Escuela Normal de Guanajuato', $alumno->entidad_procedencia);
    }

    public function test_encargado_no_puede_editar_alumno_de_otra_sede(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $alumno = Alumno::query()->where('matricula', '252206940000')->firstOrFail();
        $sedeOtra = Sede::query()->create(['nombre' => 'Otra', 'clave' => 'CCT-OTRA-2']);

        $encargado = User::factory()->create([
            'role' => 'encargado',
            'sede_id' => $sedeOtra->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($encargado)
            ->get(route('admin.alumnos.edit', $alumno))
            ->assertForbidden();
    }

    public function test_admin_puede_crear_alumno_sin_matricula(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->firstOrFail();
        $grupo = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'licenciatura' => 'TELESECUNDARIA',
        ])->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.alumnos.store'), [
                'grupo_id' => $grupo->id,
                'matricula' => '',
                'curp' => 'SIMA060315MQTRLNA3',
                'nombres' => 'Sin',
                'apellido_paterno' => 'Matricula',
                'apellido_materno' => null,
                'fecha_nacimiento' => '2006-03-15',
                'email_institucional' => 'sin.matricula@ensq.edu.mx',
                'estatus' => 'regular',
                'tipo_ingreso' => 'nuevo',
                'activo' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $alumno = Alumno::query()
            ->where('email_institucional', 'sin.matricula@ensq.edu.mx')
            ->firstOrFail();

        $this->assertNull($alumno->matricula);
        $this->assertTrue($alumno->user?->isAlumno());
    }
}
