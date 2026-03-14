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
        Schema::create('whatsapp_official_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key');
            $table->string('meta_template_name');
            $table->string('provider', 64)->default('whatsapp_business');
            $table->string('category', 32)->default('UTILITY');
            $table->string('language', 16)->default('pt_BR');
            $table->text('header_text')->nullable();
            $table->longText('body_text');
            $table->text('footer_text')->nullable();
            $table->json('buttons')->nullable();
            $table->json('variables')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'archived'])->default('draft');
            $table->string('meta_template_id')->nullable();
            $table->string('meta_waba_id')->nullable();
            $table->json('meta_response')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'key', 'version'], 'wa_official_templates_provider_key_version_unique');
            $table->unique(
                ['provider', 'meta_template_name', 'language', 'version'],
                'wa_official_templates_provider_name_lang_version_unique'
            );
            $table->index(['provider', 'status'], 'wa_official_templates_provider_status_idx');
            $table->index(['provider', 'key', 'status'], 'wa_official_templates_provider_key_status_idx');
            $table->index('meta_template_name', 'wa_official_templates_meta_name_idx');
            $table->index('last_synced_at', 'wa_official_templates_last_synced_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_official_templates');
    }
};

