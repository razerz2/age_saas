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
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('channel', 32); // email|whatsapp
            $table->string('key'); // appointment.confirmed, waitlist.offered, ...
            $table->string('provider', 120)->nullable(); // smtp/ses/waha/...
            $table->string('status', 16); // success|error
            $table->dateTime('sent_at');
            $table->string('recipient')->nullable();
            $table->text('subject')->nullable();
            $table->string('subject_sha256', 64)->nullable();
            $table->string('message_sha256', 64)->nullable();
            $table->integer('message_length')->nullable();
            $table->text('error_message')->nullable();
            $table->string('error_code', 80)->nullable();
            $table->json('meta')->nullable();

            // Optional raw payloads (filled only when NOTIFICATION_STORE_BODY=true)
            $table->longText('subject_raw')->nullable();
            $table->longText('message_raw')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'sent_at'], 'notification_deliveries_tenant_sent_at_idx');
            $table->index(['tenant_id', 'channel', 'key'], 'notification_deliveries_tenant_channel_key_idx');
            $table->index(['tenant_id', 'status'], 'notification_deliveries_tenant_status_idx');
            $table->index(['tenant_id', 'provider'], 'notification_deliveries_tenant_provider_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};

