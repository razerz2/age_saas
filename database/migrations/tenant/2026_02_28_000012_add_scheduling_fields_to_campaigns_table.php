<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campaigns')) {
            return;
        }

        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'schedule_mode')) {
                $table->string('schedule_mode', 20)->default('period')->after('automation_json');
            }

            if (!Schema::hasColumn('campaigns', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('schedule_mode');
            }

            if (!Schema::hasColumn('campaigns', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }

            if (!Schema::hasColumn('campaigns', 'schedule_weekdays')) {
                $table->json('schedule_weekdays')->nullable()->after('ends_at');
            }

            if (!Schema::hasColumn('campaigns', 'schedule_times')) {
                $table->json('schedule_times')->nullable()->after('schedule_weekdays');
            }

            if (!Schema::hasColumn('campaigns', 'timezone')) {
                $table->string('timezone', 64)->nullable()->after('schedule_times');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('campaigns')) {
            return;
        }

        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'timezone')) {
                $table->dropColumn('timezone');
            }

            if (Schema::hasColumn('campaigns', 'schedule_times')) {
                $table->dropColumn('schedule_times');
            }

            if (Schema::hasColumn('campaigns', 'schedule_weekdays')) {
                $table->dropColumn('schedule_weekdays');
            }

            if (Schema::hasColumn('campaigns', 'ends_at')) {
                $table->dropColumn('ends_at');
            }

            if (Schema::hasColumn('campaigns', 'starts_at')) {
                $table->dropColumn('starts_at');
            }

            if (Schema::hasColumn('campaigns', 'schedule_mode')) {
                $table->dropColumn('schedule_mode');
            }
        });
    }
};
