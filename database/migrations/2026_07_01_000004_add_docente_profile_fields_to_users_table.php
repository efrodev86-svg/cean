<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('name');
            $table->string('primer_apellido')->nullable()->after('nombre');
            $table->string('segundo_apellido')->nullable()->after('primer_apellido');
            $table->string('curp', 18)->nullable()->after('email');
            $table->string('tipo_contratacion', 30)->nullable()->after('grado_academico_id');
            $table->string('nombre_plaza')->nullable()->after('tipo_contratacion');
            $table->string('celular', 20)->nullable()->after('nombre_plaza');

            $table->unique('curp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['curp']);
            $table->dropColumn([
                'nombre',
                'primer_apellido',
                'segundo_apellido',
                'curp',
                'tipo_contratacion',
                'nombre_plaza',
                'celular',
            ]);
        });
    }
};
