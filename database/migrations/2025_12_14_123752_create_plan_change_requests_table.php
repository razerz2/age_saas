<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignUuid('current_plan_id')->constrained('plans');
            $table->foreignUuid('requested_plan_id')->constrained('plans');
            $table->enum('requested_payment_method', ['PIX', 'BOLETO', 'CREDIT_CARD', 'DEBIT_CARD'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('reason')->nullable(); // Motivo da solicitação
            $table->text('admin_notes')->nullable(); // Notas do administrador
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Usuário da plataforma que revisou
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_change_requests');
    }
};
