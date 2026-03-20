<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_reminder_dispatches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('event_key', 80);
            $table->date('reference_date');
            $table->string('status', 20)->default('pending');
            $table->jsonb('channels_sent')->default(json_encode([]));
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('dispatched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->jsonb('meta')->default(json_encode([]));
            $table->timestamps();

            $table->unique(
                ['subscription_id', 'event_key', 'reference_date'],
                'trial_reminder_dispatches_unique'
            );
            $table->index(['tenant_id', 'event_key'], 'trial_reminder_dispatches_tenant_event_idx');
            $table->index(['status', 'reference_date'], 'trial_reminder_dispatches_status_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_reminder_dispatches');
    }
};
