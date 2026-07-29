<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclos_escolares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });

        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('matricula')->nullable()->unique();
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('grado');
            $table->string('grupo');
            $table->string('curp', 18)->nullable();
            $table->date('fecha_nacimiento');
            $table->foreignId('ciclo_escolar_id')->constrained('ciclos_escolares')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('clave')->nullable();
            $table->string('grado')->nullable();
            $table->timestamps();
        });

        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->unsignedTinyInteger('bimestre');
            $table->decimal('calificacion', 4, 1);
            $table->unsignedSmallInteger('faltas')->default(0);
            $table->string('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['alumno_id', 'materia_id', 'bimestre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
        Schema::dropIfExists('materias');
        Schema::dropIfExists('alumnos');
        Schema::dropIfExists('ciclos_escolares');
    }
};
