<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('ciclo_escolar_id')->constrained('ciclos_escolares')->cascadeOnDelete();
            $table->unsignedTinyInteger('semestre');
            $table->string('letra', 4)->default('A');
            $table->string('licenciatura');
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['ciclo_escolar_id', 'semestre', 'letra', 'licenciatura'], 'grupos_ciclo_semestre_letra_lic_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
