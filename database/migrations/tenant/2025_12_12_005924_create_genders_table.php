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
        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome completo do gênero (ex: "Masculino", "Feminino", "Não-binário")
            $table->string('abbreviation', 10)->unique(); // Abreviação (ex: "M", "F", "NB")
            $table->integer('order')->default(0); // Ordem de exibição
            $table->boolean('is_active')->default(true); // Se está ativo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genders');
    }
};
