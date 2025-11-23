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
        Schema::create('integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // google_calendar, apple_calendar
            $table->boolean('is_enabled')->default(false);
            $table->jsonb('config')->default(json_encode([]));
            $table->timestamps();
        });

        Schema::create('oauth_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('integration_id')->constrained('integrations')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('calendar_sync_state', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('external_event_id')->nullable();
            $table->enum('provider', ['google', 'apple']);
            $table->timestamp('last_sync_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
        Schema::dropIfExists('oauth_accounts');
        Schema::dropIfExists('calendar_sync_state');
    }
};
