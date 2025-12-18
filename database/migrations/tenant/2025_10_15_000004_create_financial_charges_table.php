<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_charges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('appointment_id')->nullable();
            $table->uuid('patient_id');
            $table->string('asaas_customer_id')->nullable();
            $table->string('asaas_charge_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('billing_type', ['reservation', 'full'])->default('full');
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // pix, credit_card, boleto, debit_card
            $table->text('payment_link')->nullable();
            $table->enum('origin', ['public', 'portal', 'internal'])->default('internal');
            $table->timestamps();

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');

            $table->index('asaas_charge_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_charges');
    }
};

