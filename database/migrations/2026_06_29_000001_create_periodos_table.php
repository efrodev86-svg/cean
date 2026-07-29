<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_escolar_id')->constrained('ciclos_escolares')->cascadeOnDelete();
            $table->string('clave', 1); // A = semestres impares · B = semestres pares
            $table->string('nombre');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->date('fecha_entrega_calificaciones')->nullable();
            $table->date('fecha_consulta_boletas')->nullable();
            $table->boolean('activo')->default(false);
            $table->timestamps();

            $table->unique(['ciclo_escolar_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
