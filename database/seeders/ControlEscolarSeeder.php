<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\GradoAcademico;
use App\Models\Materia;
use App\Models\Sede;
use App\Models\User;
use App\Services\PlanEstudioImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ControlEscolarSeeder extends Seeder
{
    public function run(): void
    {
        $sede = Sede::query()->updateOrCreate(
            ['clave' => '22DNL0001P'],
            [
                'nombre' => 'Sede Central',
                'escuela' => 'ESCUELA NORMAL SUPERIOR DE QUERÉTARO',
                'director' => 'MTRO. ROBERTO COMPEÁN MARTÍNEZ',
                'ciudad' => 'SANTIAGO DE QUERÉTARO, QRO.',
                'activa' => true,
            ]
        );

        // Administrador global (ve todas las sedes).
        User::query()->updateOrCreate(
            ['email' => 'control@escuela.test'],
            [
                'name' => 'Control Escolar',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'sede_id' => null,
                'email_verified_at' => now(),
            ]
        );

        // Encargado de control escolar acotado a una sede.
        User::query()->updateOrCreate(
            ['email' => 'encargado.central@escuela.test'],
            [
                'name' => 'Encargada Sede Central',
                'password' => Hash::make('password'),
                'role' => 'encargado',
                'sede_id' => $sede->id,
                'email_verified_at' => now(),
            ]
        );

        GradoAcademico::query()->updateOrCreate(
            ['abreviatura' => 'Dr.'],
            ['activo' => true]
        );

        $gradoMaestro = GradoAcademico::query()->updateOrCreate(
            ['abreviatura' => 'Mtro.'],
            ['activo' => true]
        );

        $docente = User::query()->updateOrCreate(
            ['email' => 'docente@escuela.test'],
            [
                'nombre' => 'Carlos',
                'primer_apellido' => 'Hernández',
                'segundo_apellido' => 'Ruiz',
                'name' => 'Carlos Hernández Ruiz',
                'curp' => 'HERC780315HDFRRR02',
                'password' => Hash::make('password'),
                'role' => 'docente',
                'grado_academico_id' => $gradoMaestro->id,
                'tipo_contratacion' => 'base',
                'clave_plaza' => 'PLZ-ESP-01',
                'nombre_plaza' => 'Docente de asignatura — Español',
                'celular' => '4421234567',
                'sede_id' => null,
                'email_verified_at' => now(),
            ]
        );

        $docente->sedes()->syncWithoutDetaching([$sede->id]);

        $ciclo = CicloEscolar::query()->updateOrCreate(
            ['sede_id' => $sede->id, 'nombre' => '2023-2024'],
            [
                'activo' => true,
                'fecha_emision_boletas' => '2024-05-29',
            ]
        );

        $ciclo->periodos()->updateOrCreate(
            ['clave' => 'A'],
            [
                'nombre' => 'Periodo A · Agosto 2023 – Enero 2024',
                'fecha_inicio' => '2023-08-21',
                'fecha_cierre' => '2024-01-31',
                'fecha_entrega_calificaciones' => '2024-01-19',
                'fecha_consulta_boletas' => '2024-01-26',
                'activo' => false,
            ]
        );

        $ciclo->periodos()->updateOrCreate(
            ['clave' => 'B'],
            [
                'nombre' => 'Periodo B · Febrero – Julio 2024',
                'fecha_inicio' => '2024-02-05',
                'fecha_cierre' => '2024-07-12',
                'fecha_entrega_calificaciones' => '2024-05-24',
                'fecha_consulta_boletas' => '2024-05-29',
                'activo' => true,
            ]
        );

        $importService = app(PlanEstudioImportService::class);

        foreach (config('planes_estudio', []) as $plan) {
            $importService->importFromJsonFile(database_path('data/'.$plan['archivo']));
        }

        $materia = Materia::query()->firstOrCreate(
            ['nombre' => 'APRENDIZAJE EN EL SERVICIO'],
            ['grado' => '8']
        );

        $alumnoPdf = Alumno::query()->updateOrCreate(
            ['matricula' => '201559590000'],
            [
                'nombres' => 'JORGE LUIS',
                'apellido_paterno' => 'BENITEZ',
                'apellido_materno' => 'SALAZAR',
                'grado' => '8° Semestre',
                'grupo' => 'A',
                'semestre' => 8,
                'licenciatura' => 'TELESECUNDARIA',
                'fecha_nacimiento' => '2000-01-15',
                'ciclo_escolar_id' => $ciclo->id,
            ]
        );

        Calificacion::query()->updateOrCreate(
            [
                'alumno_id' => $alumnoPdf->id,
                'materia_id' => $materia->id,
                'semestre' => 8,
            ],
            [
                'calificacion' => 9.0,
                'asistencia_porcentaje' => 95,
            ]
        );

        $alumnoDemo = Alumno::query()->updateOrCreate(
            ['matricula' => '2025001'],
            [
                'nombres' => 'Ana',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'grado' => '2° Semestre',
                'grupo' => 'A',
                'semestre' => 2,
                'licenciatura' => 'TELESECUNDARIA',
                'fecha_nacimiento' => '2005-01-01',
                'ciclo_escolar_id' => $ciclo->id,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'alumno@escuela.test'],
            [
                'name' => 'Ana García López',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ALUMNO,
                'alumno_id' => $alumnoDemo->id,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );

        $materiasDemo = collect([
            'DIDÁCTICA DE LA LENGUA ESPAÑOLA',
            'DIDÁCTICA DE LAS MATEMÁTICAS',
            'TECNOLOGÍAS DE LA INFORMACIÓN',
        ])->map(fn (string $nombre) => Materia::query()->firstOrCreate(
            ['nombre' => $nombre],
            ['grado' => '2']
        ));

        foreach ($materiasDemo as $index => $materiaDemo) {
            Calificacion::query()->updateOrCreate(
                [
                    'alumno_id' => $alumnoDemo->id,
                    'materia_id' => $materiaDemo->id,
                    'semestre' => 2,
                ],
                [
                    'calificacion' => 8.5 + ($index * 0.3),
                    'asistencia_porcentaje' => 90 + $index,
                ]
            );
        }
    }
}
