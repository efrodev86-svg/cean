<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ControlEscolarSeeder::class,
            DocentesDirectorioSeeder::class,
            Reinscripcion2526BSeeder::class,
            AlumnosFicha2526BSeeder::class,
        ]);
    }
}
