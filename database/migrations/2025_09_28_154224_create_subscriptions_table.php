<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->tinyInteger('due_day'); // 1 a 28
            $table->date('billing_anchor_date')->nullable()->comment('Data de referência para cálculo de vencimento das faturas');
            $table->timestamp('recovery_started_at')->nullable()->comment('Data em que o processo de recovery foi iniciado para esta assinatura');
            $table->enum('status', ['pending', 'active', 'past_due', 'canceled', 'trialing', 'recovery_pending'])->default('pending');
            $table->boolean('auto_renew')->default(true);
            $table->enum('payment_method', ['PIX', 'BOLETO', 'CREDIT_CARD', 'DEBIT_CARD'])->default('PIX');
            $table->string('asaas_subscription_id')->nullable();
            $table->boolean('asaas_synced')->default(false);
            $table->string('asaas_sync_status')->default('pending');
            $table->timestamp('asaas_last_sync_at')->nullable();
            $table->text('asaas_last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
