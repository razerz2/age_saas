<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campaign_automation_locks')) {
            return;
        }

        Schema::table('campaign_automation_locks', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_automation_locks', 'window_key')) {
                $table->string('window_key', 16)->nullable()->after('window_date');
            }

            if (!Schema::hasColumn('campaign_automation_locks', 'timezone')) {
                $table->string('timezone', 64)->nullable()->after('window_key');
            }
        });

        $this->dropUniqueIndexSafely('campaign_automation_locks', 'campaign_automation_locks_uq');
        $this->dropIndexSafely('campaign_automation_locks', 'campaign_automation_locks_status_window_idx');

        $this->createUniqueIndexSafely('campaign_automation_locks', ['campaign_id', 'window_key'], 'campaign_automation_locks_campaign_window_uq');
        $this->createIndexSafely('campaign_automation_locks', ['status', 'window_key'], 'campaign_automation_locks_status_window_key_idx');
    }

    public function down(): void
    {
        if (!Schema::hasTable('campaign_automation_locks')) {
            return;
        }

        $this->dropUniqueIndexSafely('campaign_automation_locks', 'campaign_automation_locks_campaign_window_uq');
        $this->dropIndexSafely('campaign_automation_locks', 'campaign_automation_locks_status_window_key_idx');

        Schema::table('campaign_automation_locks', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_automation_locks', 'timezone')) {
                $table->dropColumn('timezone');
            }

            if (Schema::hasColumn('campaign_automation_locks', 'window_key')) {
                $table->dropColumn('window_key');
            }
        });

        $this->createUniqueIndexSafely('campaign_automation_locks', ['campaign_id', 'trigger', 'window_date'], 'campaign_automation_locks_uq');
        $this->createIndexSafely('campaign_automation_locks', ['status', 'window_date'], 'campaign_automation_locks_status_window_idx');
    }

    private function dropUniqueIndexSafely(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($index) {
                $table->dropUnique($index);
            });
        } catch (\Throwable) {
            // noop
        }
    }

    private function dropIndexSafely(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        } catch (\Throwable) {
            // noop
        }
    }

    /**
     * @param array<int,string> $columns
     */
    private function createUniqueIndexSafely(string $table, array $columns, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $index) {
                $table->unique($columns, $index);
            });
        } catch (\Throwable) {
            // noop
        }
    }

    /**
     * @param array<int,string> $columns
     */
    private function createIndexSafely(string $table, array $columns, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $index) {
                $table->index($columns, $index);
            });
        } catch (\Throwable) {
            // noop
        }
    }
};
