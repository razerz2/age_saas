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
        Schema::create('tenant_default_notification_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('channel', 32)->default('whatsapp');
            $table->string('key', 120);
            $table->string('title', 160);
            $table->string('category', 80);
            $table->string('language', 16)->default('pt_BR');
            $table->string('subject')->nullable();
            $table->longText('content');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['channel', 'key'],
                'tenant_default_notification_templates_channel_key_unique'
            );
            $table->index(
                ['channel', 'is_active'],
                'tenant_default_notification_templates_channel_active_idx'
            );
            $table->index('key', 'tenant_default_notification_templates_key_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_default_notification_templates');
    }
};

