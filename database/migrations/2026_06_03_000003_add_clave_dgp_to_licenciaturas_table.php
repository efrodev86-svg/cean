<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenciaturas', function (Blueprint $table) {
            $table->string('clave_dgp', 10)->nullable()->unique()->after('nombre_corto');
        });
    }

    public function down(): void
    {
        Schema::table('licenciaturas', function (Blueprint $table) {
            $table->dropUnique(['clave_dgp']);
            $table->dropColumn('clave_dgp');
        });
    }
};
