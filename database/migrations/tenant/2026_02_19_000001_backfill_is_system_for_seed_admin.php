<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::connection('tenant')->hasColumn('users', 'is_system')) {
            return;
        }

        DB::connection('tenant')
            ->table('users')
            ->where('role', 'admin')
            ->where('name', 'Administrador')
            ->where('name_full', 'Administrador do Sistema')
            ->where('telefone', '00000000000')
            ->where('email', 'like', 'admin@%')
            ->update(['is_system' => true]);
    }

    public function down(): void
    {
        if (!Schema::connection('tenant')->hasColumn('users', 'is_system')) {
            return;
        }

        DB::connection('tenant')
            ->table('users')
            ->where('role', 'admin')
            ->where('name', 'Administrador')
            ->where('name_full', 'Administrador do Sistema')
            ->where('telefone', '00000000000')
            ->where('email', 'like', 'admin@%')
            ->update(['is_system' => false]);
    }
};

