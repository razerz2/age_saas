<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection('tenant')->hasTable('google_calendar_tokens')) {
            return;
        }

        $duplicateDoctorIds = DB::connection('tenant')
            ->table('google_calendar_tokens')
            ->select('doctor_id')
            ->groupBy('doctor_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('doctor_id');

        foreach ($duplicateDoctorIds as $doctorId) {
            $ids = DB::connection('tenant')
                ->table('google_calendar_tokens')
                ->where('doctor_id', $doctorId)
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->pluck('id')
                ->values();

            $keepId = $ids->first();
            if (!$keepId) {
                continue;
            }

            DB::connection('tenant')
                ->table('google_calendar_tokens')
                ->where('doctor_id', $doctorId)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        try {
            Schema::connection('tenant')->table('google_calendar_tokens', function (Blueprint $table) {
                $table->unique('doctor_id', 'google_calendar_tokens_doctor_id_unique');
            });
        } catch (\Throwable $e) {
            // Constraint already exists or database does not support this operation as expected.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::connection('tenant')->hasTable('google_calendar_tokens')) {
            return;
        }

        try {
            Schema::connection('tenant')->table('google_calendar_tokens', function (Blueprint $table) {
                $table->dropUnique('google_calendar_tokens_doctor_id_unique');
            });
        } catch (\Throwable $e) {
            // No-op when the unique constraint does not exist.
        }
    }
};
