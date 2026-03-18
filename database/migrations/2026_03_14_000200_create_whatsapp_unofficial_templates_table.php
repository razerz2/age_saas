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
        Schema::create('whatsapp_unofficial_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key', 120)->unique();
            $table->string('title', 160);
            $table->string('category', 80);
            $table->longText('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active'], 'wa_unofficial_templates_category_active_idx');
            $table->index('updated_at', 'wa_unofficial_templates_updated_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_unofficial_templates');
    }
};
