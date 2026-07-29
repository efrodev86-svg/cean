<?php

use App\Support\AlumnoTipoIngreso;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('tipo_ingreso', 20)->default(AlumnoTipoIngreso::NUEVO)->after('estatus');
            $table->string('entidad_procedencia')->nullable()->after('tipo_ingreso');
            $table->string('ciudad_procedencia')->nullable()->after('entidad_procedencia');
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn(['tipo_ingreso', 'entidad_procedencia', 'ciudad_procedencia']);
        });
    }
};
