<?php

use App\Support\AlumnoEstatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('estatus', 20)->default(AlumnoEstatus::REGULAR)->after('lugar_trabajo');
        });

        if (Schema::hasColumn('alumnos', 'es_irregular')) {
            DB::table('alumnos')
                ->where('es_irregular', true)
                ->update(['estatus' => AlumnoEstatus::IRREGULAR]);
        }

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn(['es_regular', 'es_irregular']);
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->boolean('es_regular')->default(true)->after('lugar_trabajo');
            $table->boolean('es_irregular')->default(false)->after('es_regular');
        });

        DB::table('alumnos')
            ->where('estatus', AlumnoEstatus::IRREGULAR)
            ->update(['es_irregular' => true, 'es_regular' => false]);

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });
    }
};
