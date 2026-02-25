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
            $table->uuid('tenant_id');
            $table->string('channel', 32); // email | whatsapp
            $table->string('key'); // ex: appointment.pending_confirmation
            $table->string('subject')->nullable(); // apenas email
            $table->longText('content');
            $table->timestamps();

            $table->unique(['tenant_id', 'channel', 'key'], 'tenant_notification_templates_unique');
            $table->index(['tenant_id', 'channel'], 'tenant_notification_templates_tenant_channel_idx');
            $table->index(['tenant_id', 'key'], 'tenant_notification_templates_tenant_key_idx');
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

