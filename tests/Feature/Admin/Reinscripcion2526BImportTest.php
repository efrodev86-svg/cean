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

class Reinscripcion2526BImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_grupos_alumnos_y_accesos_desde_json(): void
    {
        $resultado = app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $this->assertSame(7, $resultado['grupos']);
        $this->assertSame(155, $resultado['alumnos']);
        $this->assertSame(155, $resultado['usuarios']);
        $this->assertSame(0, $resultado['sin_correo']);

        $sede = Sede::query()->where('clave', '22DNL0001P')->firstOrFail();
        $ciclo = CicloEscolar::query()->where('nombre', '2025-2026')->where('sede_id', $sede->id)->firstOrFail();

        $this->assertTrue($ciclo->activo);
        $this->assertSame(7, Grupo::query()->where('ciclo_escolar_id', $ciclo->id)->count());
        $this->assertSame(155, Alumno::query()->where('ciclo_escolar_id', $ciclo->id)->count());
        $this->assertSame(155, User::query()->where('role', User::ROLE_ALUMNO)->count());

        $grupo2a = Grupo::query()->where([
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => 2,
            'letra' => 'A',
            'licenciatura' => 'ESPANOL',
        ])->firstOrFail();

        $this->assertSame(21, $grupo2a->alumnos()->count());
        $this->assertSame('A', $grupo2a->alumnos()->orderBy('apellido_paterno')->first()->grupo);

        $alumno = Alumno::query()->where('matricula', '252206940000')->firstOrFail();
        $usuario = User::query()->where('alumno_id', $alumno->id)->firstOrFail();

        $this->assertSame('brendateresa.aguillon@ensq.edu.mx', $usuario->email);
        $this->assertTrue($usuario->isAlumno());
        $this->assertTrue($usuario->alumno->is($alumno));
        $this->assertSame('brendateresa.aguillon@ensq.edu.mx', $alumno->email_institucional);
        $this->assertNotNull($alumno->celular);
        $this->assertSame('regular', $alumno->estatus);

        $irregular = Alumno::query()->where('matricula', '252207170000')->firstOrFail();
        $this->assertSame('irregular', $irregular->estatus);
    }

    public function test_alumno_importado_puede_autenticarse_con_matricula(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $usuario = User::query()->where('email', 'brendateresa.aguillon@ensq.edu.mx')->firstOrFail();

        $this->assertTrue($usuario->isAlumno());
        $this->assertSame('252206940000', $usuario->alumno?->matricula);

        $response = $this->post('/login', [
            'email' => $usuario->email,
            'password' => '252206940000',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('alumno.dashboard', absolute: false));
    }

    public function test_alumno_con_correo_personal_usa_ese_correo(): void
    {
        app(Reinscripcion2526BImportService::class)
            ->importFromJsonFile(database_path('data/reinscripcion-2526b.json'));

        $usuario = User::query()->where('email', 'alejandrapaynesmith@gmail.com')->firstOrFail();

        $this->assertTrue($usuario->isAlumno());
        $this->assertSame(8, $usuario->alumno?->semestre);
    }
}
