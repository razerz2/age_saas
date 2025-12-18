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
        Schema::create('email_layouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->default('default')->unique()->comment('Nome do layout (ex: default)');
            $table->string('display_name')->default('Layout Padrão')->comment('Nome legível');
            $table->string('logo_url')->nullable()->comment('URL do logo para usar no cabeçalho');
            $table->integer('logo_width')->default(200)->comment('Largura do logo em pixels (padrão: 200px)');
            $table->integer('logo_height')->nullable()->comment('Altura do logo em pixels (null = proporcional)');
            $table->longText('header')->nullable()->comment('HTML do cabeçalho');
            $table->longText('footer')->nullable()->comment('HTML do rodapé');
            $table->string('primary_color')->default('#667eea')->comment('Cor primária do layout');
            $table->string('secondary_color')->default('#764ba2')->comment('Cor secundária do layout');
            $table->string('background_color')->default('#f8f9fa')->comment('Cor de fundo');
            $table->string('text_color')->default('#333333')->comment('Cor do texto');
            $table->boolean('is_active')->default(true)->comment('Layout ativo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_layouts');
    }
};
