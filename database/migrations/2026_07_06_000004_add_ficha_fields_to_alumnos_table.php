<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('referencia_pago')->nullable()->after('curp');
            $table->string('email_institucional')->nullable()->after('referencia_pago');
            $table->string('email_personal')->nullable()->after('email_institucional');
            $table->string('domicilio')->nullable()->after('email_personal');
            $table->string('colonia')->nullable()->after('domicilio');
            $table->string('codigo_postal', 10)->nullable()->after('colonia');
            $table->string('estado')->nullable()->after('codigo_postal');
            $table->string('municipio')->nullable()->after('estado');
            $table->string('celular', 20)->nullable()->after('municipio');
            $table->string('telefono_emergencia', 20)->nullable()->after('celular');
            $table->string('nss', 20)->nullable()->after('telefono_emergencia');
            $table->boolean('tiene_diagnostico')->default(false)->after('nss');
            $table->text('diagnostico_detalle')->nullable()->after('tiene_diagnostico');
            $table->boolean('tiene_discapacidad')->default(false)->after('diagnostico_detalle');
            $table->text('discapacidad_detalle')->nullable()->after('tiene_discapacidad');
            $table->string('estado_civil')->nullable()->after('discapacidad_detalle');
            $table->boolean('labora')->default(false)->after('estado_civil');
            $table->string('lugar_trabajo')->nullable()->after('labora');
            $table->boolean('es_regular')->default(true)->after('lugar_trabajo');
            $table->boolean('es_irregular')->default(false)->after('es_regular');
            $table->text('asignatura_adeuda')->nullable()->after('es_irregular');
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn([
                'referencia_pago',
                'email_institucional',
                'email_personal',
                'domicilio',
                'colonia',
                'codigo_postal',
                'estado',
                'municipio',
                'celular',
                'telefono_emergencia',
                'nss',
                'tiene_diagnostico',
                'diagnostico_detalle',
                'tiene_discapacidad',
                'discapacidad_detalle',
                'estado_civil',
                'labora',
                'lugar_trabajo',
                'es_regular',
                'es_irregular',
                'asignatura_adeuda',
            ]);
        });
    }
};
