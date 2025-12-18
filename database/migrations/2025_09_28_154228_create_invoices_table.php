<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->integer('amount_cents')->unsigned();
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable()->comment('Data e hora em que a fatura foi paga');
            $table->timestamp('notified_upcoming_at')->nullable()->comment('Data da última notificação preventiva enviada');
            $table->enum('status', ['pending','paid','overdue','canceled'])->default('pending');
            $table->boolean('is_recovery')->default(false)->comment('Indica se é invoice de recovery');
            $table->uuid('recovery_origin_subscription_id')->nullable()->comment('ID da subscription original que deu origem ao recovery');
            $table->uuid('recovery_target_subscription_id')->nullable()->comment('ID da subscription recovery_pending vinculada');
            $table->string('payment_link')->nullable();
            $table->enum('payment_method', ['PIX', 'BOLETO', 'CREDIT_CARD', 'DEBIT_CARD'])->default('PIX');
            $table->string('provider')->nullable();    // ex: asaas, pagarme
            $table->string('provider_id')->nullable(); // id no gateway
            $table->string('asaas_payment_id')->nullable();
            $table->string('asaas_payment_link_id')->nullable()->comment('ID do payment link do Asaas (para recovery)');
            $table->string('asaas_recovery_subscription_id')->nullable()->comment('ID da nova assinatura criada no Asaas após pagamento do recovery');
            $table->boolean('asaas_synced')->default(false);
            $table->string('asaas_sync_status')->default('pending');
            $table->timestamp('asaas_last_sync_at')->nullable();
            $table->text('asaas_last_error')->nullable();
            $table->timestamps();

            $table->foreign('recovery_origin_subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
            $table->foreign('recovery_target_subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
