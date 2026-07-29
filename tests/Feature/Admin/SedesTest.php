<?php

namespace Tests\Feature\Admin;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SedesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_admin_puede_registrar_una_sede(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.sedes.store'), [
                'nombre' => 'Sede Norte',
                'clave' => '22DNL0002Q',
                'director' => 'MTRA. LAURA PÉREZ',
                'ciudad' => 'SAN JUAN DEL RÍO, QRO.',
            ])
            ->assertRedirect(route('admin.sedes.index'));

        $this->assertDatabaseHas('sedes', [
            'clave' => '22DNL0002Q',
            'director' => 'MTRA. LAURA PÉREZ',
        ]);
    }

    public function test_admin_puede_subir_logo_de_sede(): void
    {
        $sede = Sede::query()->create([
            'nombre' => 'Sede Sur',
            'clave' => '22DNL0003R',
            'activa' => true,
        ]);

        $archivo = UploadedFile::fake()->image('logo-sede.png', 200, 200);

        $this->actingAs($this->admin())
            ->patch(route('admin.sedes.update', $sede), [
                'nombre' => $sede->nombre,
                'clave' => $sede->clave,
                'activa' => '1',
                'logo' => $archivo,
            ])
            ->assertRedirect(route('admin.sedes.index'));

        $sede->refresh();
        $this->assertNotNull($sede->logo);
        $this->assertStringStartsWith('images/sedes/', $sede->logo);
        $this->assertFileExists(public_path($sede->logo));
    }

    public function test_boleta_usa_datos_institucionales_de_la_sede_del_alumno(): void
    {
        $sede = Sede::query()->create([
            'nombre' => 'Sede Norte',
            'clave' => '22DNL0002Q',
            'escuela' => 'NORMAL SUPERIOR — CAMPUS NORTE',
            'director' => 'MTRA. LAURA PÉREZ GÓMEZ',
            'ciudad' => 'SAN JUAN DEL RÍO, QRO.',
        ]);

        $ciclo = CicloEscolar::query()->create([
            'sede_id' => $sede->id,
            'nombre' => '2024-2025',
            'activo' => true,
            'fecha_emision_boletas' => '2025-05-30',
        ]);

        $alumno = Alumno::query()->create([
            'matricula' => '2025010',
            'nombres' => 'LUIS',
            'apellido_paterno' => 'TORRES',
            'apellido_materno' => 'DÍAZ',
            'grado' => '4° Semestre',
            'grupo' => 'A',
            'semestre' => 4,
            'licenciatura' => 'TELESECUNDARIA',
            'fecha_nacimiento' => '2004-03-10',
            'ciclo_escolar_id' => $ciclo->id,
        ]);

        $materia = Materia::query()->create(['nombre' => 'DIDÁCTICA']);
        Calificacion::query()->create([
            'alumno_id' => $alumno->id,
            'materia_id' => $materia->id,
            'semestre' => 4,
            'calificacion' => 9.0,
            'asistencia_porcentaje' => 95,
        ]);

        $response = $this->post(route('boleta.consultar'), [
            'matricula' => '2025010',
            'fecha_nacimiento' => '2004-03-10',
        ]);

        $response->assertOk();
        $response->assertSee('NORMAL SUPERIOR — CAMPUS NORTE');
        $response->assertSee('MTRA. LAURA PÉREZ GÓMEZ');
        $response->assertSee('SAN JUAN DEL RÍO, QRO.');
    }
}
