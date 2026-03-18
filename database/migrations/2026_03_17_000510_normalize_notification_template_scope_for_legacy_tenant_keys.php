<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('notification_templates') || !Schema::hasColumn('notification_templates', 'scope')) {
            return;
        }

        $tenantBaselineKeys = [
            'appointment.pending_confirmation',
            'appointment.confirmed',
            'appointment.canceled',
            'appointment.expired',
            'waitlist.joined',
            'waitlist.offered',
            // Compatibilidade defensiva para legados em snake_case.
            'appointment_pending_confirmation',
            'appointment_confirmed',
            'appointment_canceled',
            'appointment_expired',
            'waitlist_joined',
            'waitlist_offered',
        ];

        DB::table('notification_templates')
            ->where('channel', 'email')
            ->where('scope', 'platform')
            ->whereIn('name', $tenantBaselineKeys)
            ->update([
                'scope' => 'tenant',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('notification_templates') || !Schema::hasColumn('notification_templates', 'scope')) {
            return;
        }

        $tenantBaselineKeys = [
            'appointment.pending_confirmation',
            'appointment.confirmed',
            'appointment.canceled',
            'appointment.expired',
            'waitlist.joined',
            'waitlist.offered',
            'appointment_pending_confirmation',
            'appointment_confirmed',
            'appointment_canceled',
            'appointment_expired',
            'waitlist_joined',
            'waitlist_offered',
        ];

        DB::table('notification_templates')
            ->where('channel', 'email')
            ->where('scope', 'tenant')
            ->whereIn('name', $tenantBaselineKeys)
            ->update([
                'scope' => 'platform',
            ]);
    }
};
