<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications_outbox', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->enum('channel', ['email','whatsapp','sms','inapp']);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->jsonb('meta')->default(json_encode([]));
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['queued','sent','error'])->default('queued');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('notifications_outbox');
    }
};
