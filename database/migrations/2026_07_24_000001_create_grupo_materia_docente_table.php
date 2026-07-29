<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_materia_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('docente_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['grupo_id', 'materia_id']);
            $table->index(['docente_id', 'grupo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_materia_docente');
    }
};
