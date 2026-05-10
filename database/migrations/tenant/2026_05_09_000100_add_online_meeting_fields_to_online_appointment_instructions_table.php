<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_appointment_instructions', function (Blueprint $table) {
            if (!Schema::hasColumn('online_appointment_instructions', 'meeting_provider')) {
                $table->string('meeting_provider')->nullable()->after('meeting_app');
                $table->index('meeting_provider');
            }

            if (!Schema::hasColumn('online_appointment_instructions', 'meeting_status')) {
                $table->string('meeting_status')->nullable()->after('meeting_provider');
                $table->index('meeting_status');
            }

            if (!Schema::hasColumn('online_appointment_instructions', 'external_event_id')) {
                $table->string('external_event_id')->nullable()->after('meeting_status');
                $table->index('external_event_id');
            }

            if (!Schema::hasColumn('online_appointment_instructions', 'external_meeting_id')) {
                $table->string('external_meeting_id')->nullable()->after('external_event_id');
            }

            if (!Schema::hasColumn('online_appointment_instructions', 'meeting_generated_at')) {
                $table->timestamp('meeting_generated_at')->nullable()->after('external_meeting_id');
            }

            if (!Schema::hasColumn('online_appointment_instructions', 'meeting_generation_error')) {
                $table->text('meeting_generation_error')->nullable()->after('meeting_generated_at');
            }

            if (!Schema::hasColumn('online_appointment_instructions', 'meeting_meta')) {
                $table->json('meeting_meta')->nullable()->after('meeting_generation_error');
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_appointment_instructions', function (Blueprint $table) {
            if (Schema::hasColumn('online_appointment_instructions', 'meeting_provider')) {
                $table->dropIndex(['meeting_provider']);
                $table->dropColumn('meeting_provider');
            }

            if (Schema::hasColumn('online_appointment_instructions', 'meeting_status')) {
                $table->dropIndex(['meeting_status']);
                $table->dropColumn('meeting_status');
            }

            if (Schema::hasColumn('online_appointment_instructions', 'external_event_id')) {
                $table->dropIndex(['external_event_id']);
                $table->dropColumn('external_event_id');
            }

            if (Schema::hasColumn('online_appointment_instructions', 'external_meeting_id')) {
                $table->dropColumn('external_meeting_id');
            }

            if (Schema::hasColumn('online_appointment_instructions', 'meeting_generated_at')) {
                $table->dropColumn('meeting_generated_at');
            }

            if (Schema::hasColumn('online_appointment_instructions', 'meeting_generation_error')) {
                $table->dropColumn('meeting_generation_error');
            }

            if (Schema::hasColumn('online_appointment_instructions', 'meeting_meta')) {
                $table->dropColumn('meeting_meta');
            }
        });
    }
};

