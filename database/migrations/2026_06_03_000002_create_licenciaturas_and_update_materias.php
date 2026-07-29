<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenciaturas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_corto')->unique();
            $table->string('nombre');
            $table->string('plan_nombre')->nullable();
            $table->unsignedSmallInteger('anio_plan')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->foreignId('licenciatura_id')->nullable()->after('id')->constrained('licenciaturas')->nullOnDelete();
            $table->unsignedTinyInteger('semestre')->nullable()->after('clave');
            $table->unsignedTinyInteger('orden')->nullable()->after('semestre');
            $table->decimal('creditos', 5, 2)->nullable()->after('orden');
            $table->decimal('horas_semana', 5, 2)->nullable()->after('creditos');
            $table->decimal('horas_semestre', 6, 2)->nullable()->after('horas_semana');

            $table->unique(['licenciatura_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropUnique(['licenciatura_id', 'clave']);
            $table->dropConstrainedForeignId('licenciatura_id');
            $table->dropColumn(['semestre', 'orden', 'creditos', 'horas_semana', 'horas_semestre']);
        });

        Schema::dropIfExists('licenciaturas');
    }
};
