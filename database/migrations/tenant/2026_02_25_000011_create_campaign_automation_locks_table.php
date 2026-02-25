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
        if (Schema::hasTable('campaign_automation_locks')) {
            return;
        }

        Schema::create('campaign_automation_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('trigger', 50);
            $table->date('window_date');
            $table->string('status', 20)->default('locked'); // locked|done|error
            $table->foreignId('run_id')->nullable()->constrained('campaign_runs')->nullOnDelete();
            $table->string('error_message', 500)->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'trigger', 'window_date'], 'campaign_automation_locks_uq');
            $table->index(['status', 'window_date'], 'campaign_automation_locks_status_window_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_automation_locks');
    }
};
