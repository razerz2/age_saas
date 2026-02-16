<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remover coluna network_id da tabela tenants (se existir)
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'network_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                // Remover foreign key e índices associados, se existirem
                try {
                    // Tenta pelos nomes mais comuns de constraint
                    $table->dropForeign(['network_id']);
                } catch (\Throwable $e) {
                    // Ignora caso a foreign key não exista ou já tenha sido removida
                }

                try {
                    $table->dropIndex(['network_id']);
                } catch (\Throwable $e) {
                    // Ignora caso o índice não exista
                }

                // Remover a coluna em si
                $table->dropColumn('network_id');
            });
        }

        // Drop da tabela network_users primeiro (possui FK para clinic_networks em PostgreSQL)
        if (Schema::hasTable('network_users')) {
            Schema::dropIfExists('network_users');
        }

        // Depois, drop da tabela clinic_networks, se existir
        if (Schema::hasTable('clinic_networks')) {
            Schema::dropIfExists('clinic_networks');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intencionalmente vazio: estrutura de rede não será recriada
    }
};
