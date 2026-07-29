<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Los administradores acotados a una sede pasan a ser "encargado" (control escolar de sede).
        DB::table('users')
            ->where('role', 'admin')
            ->whereNotNull('sede_id')
            ->update(['role' => 'encargado']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'encargado')
            ->update(['role' => 'admin']);
    }
};
