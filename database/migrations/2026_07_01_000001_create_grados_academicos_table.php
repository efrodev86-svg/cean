<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grados_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('abreviatura', 20);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('abreviatura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grados_academicos');
    }
};
