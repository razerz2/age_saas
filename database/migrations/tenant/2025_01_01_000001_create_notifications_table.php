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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // 'appointment', 'form_response'
            $table->string('title');
            $table->text('message');
            $table->enum('level', ['info', 'warning', 'error', 'success'])->default('info');
            $table->enum('status', ['new', 'read'])->default('new');
            
            // Relacionamentos opcionais
            $table->uuid('related_id')->nullable(); // ID do agendamento ou resposta de formulário
            $table->string('related_type')->nullable(); // 'Appointment', 'FormResponse'
            
            $table->json('metadata')->nullable(); // Dados adicionais
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
            
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

