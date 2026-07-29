<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ciclos_escolares', function (Blueprint $table) {
            $table->date('fecha_emision_boletas')->nullable()->after('activo');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->unsignedTinyInteger('semestre')->default(1)->after('grupo');
            $table->string('licenciatura')->default('TELESECUNDARIA')->after('semestre');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->renameColumn('bimestre', 'semestre');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->unsignedTinyInteger('asistencia_porcentaje')->default(100)->after('calificacion');
            $table->dropColumn('faltas');
        });
    }

    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->unsignedSmallInteger('faltas')->default(0)->after('calificacion');
            $table->dropColumn('asistencia_porcentaje');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->renameColumn('semestre', 'bimestre');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn(['semestre', 'licenciatura']);
        });

        Schema::table('ciclos_escolares', function (Blueprint $table) {
            $table->dropColumn('fecha_emision_boletas');
        });
    }
};
