<?php

use App\Models\Tenant\TenantSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            TenantSetting::set('appointments.confirmation.enabled', 'false');
            TenantSetting::set('appointments.confirmation.ttl_minutes', '30');

            TenantSetting::set('appointments.waitlist.enabled', 'false');
            TenantSetting::set('appointments.waitlist.offer_ttl_minutes', '15');
            TenantSetting::set('appointments.waitlist.allow_when_confirmed', 'true');
            TenantSetting::set('appointments.waitlist.max_per_slot', null);
        } catch (\Exception $e) {
            // Ignora erro em cenarios de bootstrap incompleto do tenant.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            TenantSetting::whereIn('key', [
                'appointments.confirmation.enabled',
                'appointments.confirmation.ttl_minutes',
                'appointments.waitlist.enabled',
                'appointments.waitlist.offer_ttl_minutes',
                'appointments.waitlist.allow_when_confirmed',
                'appointments.waitlist.max_per_slot',
            ])->delete();
        } catch (\Exception $e) {
            // Ignora erro para rollback resiliente.
        }
    }
};

