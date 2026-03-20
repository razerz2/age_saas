<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'trial_enabled')) {
                $table->boolean('trial_enabled')
                    ->default(false)
                    ->after('show_on_landing_page');
            }

            if (! Schema::hasColumn('plans', 'trial_days')) {
                $table->unsignedSmallInteger('trial_days')
                    ->nullable()
                    ->after('trial_enabled');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'is_trial')) {
                $table->boolean('is_trial')
                    ->default(false)
                    ->after('payment_method');
            }

            if (! Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')
                    ->nullable()
                    ->after('is_trial');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $columnsToDrop[] = 'trial_ends_at';
            }

            if (Schema::hasColumn('subscriptions', 'is_trial')) {
                $columnsToDrop[] = 'is_trial';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('plans', 'trial_days')) {
                $columnsToDrop[] = 'trial_days';
            }

            if (Schema::hasColumn('plans', 'trial_enabled')) {
                $columnsToDrop[] = 'trial_enabled';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
