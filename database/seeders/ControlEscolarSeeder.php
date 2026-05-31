<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ControlEscolarSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'control@escuela.test'],
            [
                'name' => 'Control Escolar',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $ciclo = CicloEscolar::query()->updateOrCreate(
            ['nombre' => '2025-2026'],
            ['activo' => true]
        );

        $materias = collect([
            'Español',
            'Matemáticas',
            'Ciencias',
            'Historia',
            'Formación Cívica y Ética',
        ])->map(fn (string $nombre) => Materia::query()->firstOrCreate(
            ['nombre' => $nombre],
            ['grado' => '1° Secundaria']
        ));

        $alumnos = [
            [
                'matricula' => '2025001',
                'nombres' => 'Ana',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'grado' => '1° Secundaria',
                'grupo' => 'A',
                'curp' => 'GALA050101MDFRRN09',
                'fecha_nacimiento' => '2005-01-01',
            ],
            [
                'matricula' => '2025002',
                'nombres' => 'Carlos',
                'apellido_paterno' => 'Martínez',
                'apellido_materno' => 'Ruiz',
                'grado' => '1° Secundaria',
                'grupo' => 'A',
                'curp' => 'MARCO050215HDFRRL08',
                'fecha_nacimiento' => '2005-02-15',
            ],
        ];

        foreach ($alumnos as $datos) {
            $alumno = Alumno::query()->updateOrCreate(
                ['matricula' => $datos['matricula']],
                array_merge($datos, ['ciclo_escolar_id' => $ciclo->id])
            );

            foreach ($materias as $index => $materia) {
                Calificacion::query()->updateOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'materia_id' => $materia->id,
                        'bimestre' => 1,
                    ],
                    [
                        'calificacion' => 8.5 + ($index * 0.2),
                        'faltas' => $index,
                    ]
                );
            }
        }
    }
}
