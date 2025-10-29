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
            $table->enum('status', ['pending','paid','overdue','canceled'])->default('pending');
            $table->string('payment_link')->nullable();
            $table->enum('payment_method', ['PIX', 'BOLETO', 'CREDIT_CARD', 'DEBIT_CARD'])->default('PIX');
            $table->string('provider')->nullable();    // ex: asaas, pagarme
            $table->string('provider_id')->nullable(); // id no gateway
            $table->string('asaas_payment_id')->nullable();
            $table->boolean('asaas_synced')->default(false);
            $table->string('asaas_sync_status')->default('pending');
            $table->timestamp('asaas_last_sync_at')->nullable();
            $table->text('asaas_last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
