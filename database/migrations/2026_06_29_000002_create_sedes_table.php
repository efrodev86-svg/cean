<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('clave')->unique(); // CCT / clave oficial
            $table->string('escuela')->nullable();   // override del nombre en la boleta
            $table->string('director')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('logo')->nullable();       // ruta relativa en public/ (override del logo de boleta)
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};
