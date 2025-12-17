<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos para ledger financeiro e valores brutos/líquidos
     * Todos os campos são nullable para compatibilidade com dados existentes
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            // Campos de origem do lançamento (ledger)
            $table->string('origin_type')->nullable()->after('type');
            $table->uuid('origin_id')->nullable()->after('origin_type');
            $table->enum('direction', ['credit', 'debit'])->nullable()->after('origin_id');
            
            // Campos de valores brutos, taxas e líquidos
            $table->decimal('gross_amount', 15, 2)->nullable()->after('amount');
            $table->decimal('gateway_fee', 15, 2)->default(0)->after('gross_amount');
            $table->decimal('net_amount', 15, 2)->nullable()->after('gateway_fee');
            
            // Campo para metadata (idempotência, dados adicionais)
            if (!Schema::hasColumn('financial_transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('created_by');
            }
            
            // Índices para melhor performance em consultas
            $table->index('origin_type');
            $table->index(['origin_type', 'origin_id']);
            $table->index('direction');
        });
        
        // Preencher campos existentes com valores padrão
        // direction baseado em type
        DB::statement("UPDATE financial_transactions SET direction = CASE WHEN type = 'income' THEN 'credit' ELSE 'debit' END WHERE direction IS NULL");
        
        // Preencher gross_amount e net_amount com amount existente
        DB::statement("UPDATE financial_transactions SET gross_amount = amount, net_amount = amount WHERE gross_amount IS NULL");
        
        // Preencher origin_type baseado em campos existentes
        DB::statement("UPDATE financial_transactions SET origin_type = 'appointment' WHERE appointment_id IS NOT NULL AND origin_type IS NULL");
        DB::statement("UPDATE financial_transactions SET origin_type = 'manual' WHERE appointment_id IS NULL AND origin_type IS NULL");
        
        // Preencher origin_id baseado em origin_type
        DB::statement("UPDATE financial_transactions SET origin_id = appointment_id WHERE origin_type = 'appointment' AND origin_id IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['origin_type', 'origin_id']);
            $table->dropIndex(['origin_type']);
            $table->dropIndex(['direction']);
            
            $columnsToDrop = [
                'origin_type',
                'origin_id',
                'direction',
                'gross_amount',
                'gateway_fee',
                'net_amount',
            ];
            
            // Só remover metadata se foi criado nesta migration
            if (Schema::hasColumn('financial_transactions', 'metadata')) {
                // Verificar se metadata foi criado nesta migration (não remover se já existia)
                // Por segurança, não removemos metadata no down()
            }
            
            $table->dropColumn($columnsToDrop);
        });
    }
};
