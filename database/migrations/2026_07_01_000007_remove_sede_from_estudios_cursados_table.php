<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('estudios_cursados', 'sede_id')) {
            return;
        }

        Schema::table('estudios_cursados', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
        });

        Schema::table('estudios_cursados', function (Blueprint $table) {
            $table->dropIndex('estudios_cursados_user_id_sede_id_index');
            $table->dropColumn('sede_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('estudios_cursados', 'sede_id')) {
            return;
        }

        Schema::table('estudios_cursados', function (Blueprint $table) {
            $table->foreignId('sede_id')->nullable()->constrained()->cascadeOnDelete();
            $table->index(['user_id', 'sede_id']);
        });
    }
};
