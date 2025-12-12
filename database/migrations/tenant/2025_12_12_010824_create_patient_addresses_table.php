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
        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            
            // Dados do endereço
            $table->string('postal_code', 10)->nullable(); // CEP
            $table->string('street')->nullable(); // Logradouro/Rua
            $table->string('number', 20)->nullable(); // Número
            $table->string('complement')->nullable(); // Complemento
            $table->string('neighborhood')->nullable(); // Bairro
            $table->string('city')->nullable(); // Cidade
            $table->string('state', 2)->nullable(); // Estado (UF)
            $table->string('country', 100)->default('Brasil'); // País
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_addresses');
    }
};
