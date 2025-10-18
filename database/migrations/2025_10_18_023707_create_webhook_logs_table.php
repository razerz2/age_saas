<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider')->default('asaas');
            $table->string('event')->nullable();
            $table->string('invoice_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->jsonb('payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};