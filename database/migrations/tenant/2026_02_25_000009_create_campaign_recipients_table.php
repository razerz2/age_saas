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
        if (Schema::hasTable('campaign_recipients')) {
            return;
        }

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('campaign_run_id')->constrained('campaign_runs')->cascadeOnDelete();

            $table->string('target_type', 50); // ex patient
            $table->unsignedBigInteger('target_id')->nullable();

            $table->string('channel', 20); // email|whatsapp
            $table->string('destination', 255); // email ou telefone E.164
            $table->string('status', 20); // pending|sent|error|skipped

            $table->timestamp('sent_at')->nullable();
            $table->string('error_message', 500)->nullable();

            $table->json('vars_json')->nullable(); // snapshot variáveis do destinatário
            $table->json('meta_json')->nullable(); // delivery_id, provider, etc.

            $table->timestamps();

            $table->unique(['campaign_run_id', 'channel', 'destination'], 'campaign_recipients_run_channel_dest_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};

