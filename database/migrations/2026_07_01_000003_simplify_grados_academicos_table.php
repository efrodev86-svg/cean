<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('grados_academicos', 'nombre') ? 'nombre' : null,
            Schema::hasColumn('grados_academicos', 'orden') ? 'orden' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('grados_academicos', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::table('grados_academicos', function (Blueprint $table) {
            if (! Schema::hasColumn('grados_academicos', 'nombre')) {
                $table->string('nombre')->default('');
            }
            if (! Schema::hasColumn('grados_academicos', 'orden')) {
                $table->unsignedSmallInteger('orden')->default(0);
            }
        });
    }
};
