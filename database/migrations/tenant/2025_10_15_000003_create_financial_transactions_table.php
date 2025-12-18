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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['income', 'expense'])->default('income');
            $table->string('origin_type')->nullable(); // appointment, manual, etc
            $table->uuid('origin_id')->nullable();
            $table->enum('direction', ['credit', 'debit'])->nullable();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->decimal('gateway_fee', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->date('date');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->uuid('account_id')->nullable();
            $table->uuid('category_id')->nullable();
            $table->uuid('appointment_id')->nullable();
            $table->uuid('patient_id')->nullable();
            $table->uuid('doctor_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('financial_accounts')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('financial_categories')->onDelete('set null');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('date');
            $table->index('status');
            $table->index('type');
            $table->index('origin_type');
            $table->index(['origin_type', 'origin_id']);
            $table->index('direction');
        });

        // Preencher campos existentes com valores padrão (apenas se tabela já existir - para desenvolvimento)
        // Esta lógica só é executada quando a migration é executada em um ambiente com dados existentes
        try {
            // Preencher direction baseado em type
            DB::statement("UPDATE financial_transactions SET direction = CASE WHEN type = 'income' THEN 'credit' ELSE 'debit' END WHERE direction IS NULL");
            
            // Preencher gross_amount e net_amount com amount existente
            DB::statement("UPDATE financial_transactions SET gross_amount = amount, net_amount = amount WHERE gross_amount IS NULL");
            
            // Preencher origin_type baseado em campos existentes
            DB::statement("UPDATE financial_transactions SET origin_type = 'appointment' WHERE appointment_id IS NOT NULL AND origin_type IS NULL");
            DB::statement("UPDATE financial_transactions SET origin_type = 'manual' WHERE appointment_id IS NULL AND origin_type IS NULL");
            
            // Preencher origin_id baseado em origin_type
            DB::statement("UPDATE financial_transactions SET origin_id = appointment_id WHERE origin_type = 'appointment' AND origin_id IS NULL");
        } catch (\Exception $e) {
            // Ignora erros se tabela não existir (primeira execução)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};

