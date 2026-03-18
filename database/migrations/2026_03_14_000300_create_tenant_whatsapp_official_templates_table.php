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
        Schema::create('tenant_whatsapp_official_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('event_key', 120);
            $table->uuid('whatsapp_official_template_id');
            $table->string('language', 16)->default('pt_BR');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('whatsapp_official_template_id', 'tenant_whatsapp_official_templates_template_fk')
                ->references('id')
                ->on('whatsapp_official_templates')
                ->restrictOnDelete();

            $table->unique(
                ['tenant_id', 'event_key', 'language'],
                'tenant_whatsapp_official_templates_tenant_event_lang_unique'
            );

            $table->index(['tenant_id', 'is_active'], 'tenant_whatsapp_official_templates_tenant_active_idx');
            $table->index(['event_key', 'language'], 'tenant_whatsapp_official_templates_event_language_idx');
            $table->index(['whatsapp_official_template_id'], 'tenant_whatsapp_official_templates_template_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_whatsapp_official_templates');
    }
};

