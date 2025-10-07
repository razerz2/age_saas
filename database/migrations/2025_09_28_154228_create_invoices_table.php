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
            $table->string('provider')->nullable();    // ex: asaas, pagarme
            $table->string('provider_id')->nullable(); // id no gateway
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
