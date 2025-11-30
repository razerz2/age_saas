<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant\TenantSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Inserir configuração padrão para todos os tenants existentes
        // A configuração será criada automaticamente quando o tenant for criado
        // Este código garante que tenants existentes também tenham a configuração
        try {
            TenantSetting::set('appointments.default_appointment_mode', 'user_choice');
        } catch (\Exception $e) {
            // Ignora erro se a tabela ainda não existir ou se já existir
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover configuração (opcional)
        try {
            TenantSetting::where('key', 'appointments.default_appointment_mode')->delete();
        } catch (\Exception $e) {
            // Ignora erro
        }
    }
};
