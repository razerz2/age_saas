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
        // PostgreSQL não suporta INNER JOIN em UPDATE, usa FROM + WHERE
        // doctor_id é UUID, então só verificamos IS NULL (não pode comparar com string vazia)
        DB::connection('tenant')->statement("
            UPDATE appointments a
            SET doctor_id = c.doctor_id
            FROM calendars c
            WHERE a.calendar_id = c.id
            AND a.doctor_id IS NULL
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
