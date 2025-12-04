<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Preenche doctor_id em appointments que não possuem, baseado no calendar_id
     */
    public function up(): void
    {
        // Atualizar appointments que não têm doctor_id mas têm calendar_id
        // Usando DB::raw para garantir que funciona com a conexão tenant
        DB::connection('tenant')->statement("
            UPDATE appointments a
            INNER JOIN calendars c ON a.calendar_id = c.id
            SET a.doctor_id = c.doctor_id
            WHERE a.doctor_id IS NULL OR a.doctor_id = ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter esta operação de forma segura
        // A migration apenas preenche dados faltantes, não remove dados existentes
    }
};
