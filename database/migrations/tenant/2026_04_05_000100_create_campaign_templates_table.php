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
        if (Schema::hasTable('campaign_templates')) {
            return;
        }

        Schema::create('campaign_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('channel', 32)->default('whatsapp');
            $table->string('provider_type', 20)->default('unofficial');
            $table->string('template_key', 150)->nullable();
            $table->string('title', 150)->nullable();
            $table->longText('content');
            $table->json('variables_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['channel', 'provider_type'], 'campaign_templates_channel_provider_idx');
            $table->index('is_active', 'campaign_templates_is_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_templates');
    }
};

