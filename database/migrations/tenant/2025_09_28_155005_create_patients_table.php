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
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('cpf')->unique();
            $table->date('birth_date')->nullable();
            // gender_id será adicionado em migration posterior (2025_12_12_005927) após criação da tabela genders
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('asaas_customer_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index('asaas_customer_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
