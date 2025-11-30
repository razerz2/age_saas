<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a coluna não existe antes de adicionar
        if (!Schema::connection('tenant')->hasColumn('users', 'avatar')) {
            Schema::connection('tenant')->table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('tenant')->hasColumn('users', 'avatar')) {
            Schema::connection('tenant')->table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }
};
