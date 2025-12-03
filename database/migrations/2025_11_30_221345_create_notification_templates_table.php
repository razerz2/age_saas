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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->comment('Chave interna única (ex: invoice_created)');
            $table->string('display_name')->comment('Nome legível');
            $table->enum('channel', ['email', 'whatsapp'])->comment('Canal da mensagem');
            $table->string('subject')->nullable()->comment('Apenas email');
            $table->longText('body')->comment('Template customizado');
            $table->string('default_subject')->nullable()->comment('Subject padrão');
            $table->longText('default_body')->comment('Body padrão');
            $table->json('variables')->comment('Lista de variáveis suportadas');
            $table->boolean('enabled')->default(true)->comment('Template ativo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
