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
        Schema::create('doctor_billing_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->uuid('specialty_id')->nullable(); // Null quando for apenas por médico
            $table->decimal('reservation_amount', 15, 2)->default(0.00);
            $table->decimal('full_appointment_amount', 15, 2)->default(0.00);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Foreign key para specialty (apenas quando não for null)
            $table->foreign('specialty_id')->references('id')->on('medical_specialties')->onDelete('cascade');
            
            // Índice único: um médico não pode ter o mesmo preço para a mesma especialidade
            // Usando índice único parcial para permitir múltiplos nulls (um por médico)
            $table->unique(['doctor_id', 'specialty_id'], 'doctor_specialty_unique');
            
            // Índices para busca rápida
            $table->index('doctor_id');
            $table->index('specialty_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_billing_prices');
    }
};
