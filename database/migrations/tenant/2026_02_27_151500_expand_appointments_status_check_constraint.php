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
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $allowedStatuses = [
            'scheduled',
            'rescheduled',
            'canceled',
            'cancelled',
            'attended',
            'arrived',
            'in_service',
            'completed',
            'no_show',
            'confirmed',
            'pending_confirmation',
            'expired',
        ];

        $quotedStatuses = implode(', ', array_map(fn ($status) => "'" . $status . "'", $allowedStatuses));

        DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check');
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ({$quotedStatuses}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $legacyStatuses = [
            'scheduled',
            'rescheduled',
            'canceled',
            'attended',
            'no_show',
        ];

        $quotedStatuses = implode(', ', array_map(fn ($status) => "'" . $status . "'", $legacyStatuses));

        DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check');
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ({$quotedStatuses}))");
    }
};
