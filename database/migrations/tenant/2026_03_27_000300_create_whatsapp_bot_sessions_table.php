<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_bot_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('channel', 32)->default('whatsapp');
            $table->string('provider', 32)->nullable();
            $table->string('contact_phone', 32);
            $table->string('contact_identifier')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('current_flow', 100)->nullable();
            $table->string('current_step', 100)->nullable();
            $table->json('state')->nullable();
            $table->string('last_inbound_message_type', 32)->nullable();
            $table->dateTime('last_inbound_message_at')->nullable();
            $table->dateTime('last_outbound_message_at')->nullable();
            $table->json('last_payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'channel'], 'whatsapp_bot_sessions_tenant_channel_idx');
            $table->index(['tenant_id', 'provider'], 'whatsapp_bot_sessions_tenant_provider_idx');
            $table->index(['tenant_id', 'last_inbound_message_at'], 'whatsapp_bot_sessions_tenant_last_inbound_idx');
            $table->unique(['tenant_id', 'channel', 'contact_phone'], 'whatsapp_bot_sessions_tenant_channel_phone_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_bot_sessions');
    }
};

