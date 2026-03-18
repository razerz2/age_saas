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
        Schema::create('whatsapp_official_template_bindings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('scope', 20);
            $table->string('event_key', 120);
            $table->uuid('whatsapp_official_template_id');
            $table->string('provider', 50)->default('whatsapp_business');
            $table->string('language', 16)->default('pt_BR');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('whatsapp_official_template_id', 'wa_official_bindings_template_fk')
                ->references('id')
                ->on('whatsapp_official_templates')
                ->restrictOnDelete();

            $table->unique(['scope', 'event_key'], 'wa_official_bindings_scope_event_unique');
            $table->index(['scope', 'provider'], 'wa_official_bindings_scope_provider_idx');
            $table->index(['event_key', 'language'], 'wa_official_bindings_event_language_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_official_template_bindings');
    }
};

