<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'cancel_requested_at')) {
                $table->timestamp('cancel_requested_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('subscriptions', 'cancel_at_period_end')) {
                $table->boolean('cancel_at_period_end')->default(false)->after('cancel_requested_at');
            }

            if (! Schema::hasColumn('subscriptions', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancel_at_period_end');
            }

            if (! Schema::hasColumn('subscriptions', 'cancellation_requested_by')) {
                $table->string('cancellation_requested_by')->nullable()->after('cancellation_reason');
            }

            if (! Schema::hasColumn('subscriptions', 'cancellation_processed_at')) {
                $table->timestamp('cancellation_processed_at')->nullable()->after('cancellation_requested_by');
            }

            if (! Schema::hasColumn('subscriptions', 'cancellation_status')) {
                $table->string('cancellation_status')->nullable()->after('cancellation_processed_at');
                $table->index('cancellation_status', 'subscriptions_cancellation_status_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'cancellation_status')) {
                $table->dropIndex('subscriptions_cancellation_status_idx');
                $table->dropColumn('cancellation_status');
            }

            if (Schema::hasColumn('subscriptions', 'cancellation_processed_at')) {
                $table->dropColumn('cancellation_processed_at');
            }

            if (Schema::hasColumn('subscriptions', 'cancellation_requested_by')) {
                $table->dropColumn('cancellation_requested_by');
            }

            if (Schema::hasColumn('subscriptions', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }

            if (Schema::hasColumn('subscriptions', 'cancel_at_period_end')) {
                $table->dropColumn('cancel_at_period_end');
            }

            if (Schema::hasColumn('subscriptions', 'cancel_requested_at')) {
                $table->dropColumn('cancel_requested_at');
            }
        });
    }
};
