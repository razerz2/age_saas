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
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calendar_id')->constrained('calendars')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignUuid('appointment_type')->nullable()->constrained('appointment_types');
            $table->foreignUuid('patient_id')->constrained('patients');
            $table->uuid('recurring_appointment_id')->nullable()->after('patient_id');
            // Foreign key será adicionada na migration de recurring_appointments
            $table->foreignUuid('specialty_id')->nullable()->constrained('medical_specialties');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['scheduled', 'rescheduled', 'canceled', 'attended', 'no_show'])->default('scheduled');
            $table->enum('appointment_mode', ['presencial', 'online'])->default('presencial')->after('status');
            $table->enum('origin', ['public', 'portal', 'internal'])->default('internal')->after('appointment_mode');
            $table->text('notes')->nullable();

            $table->index('origin');
            $table->string('google_event_id')->nullable();
            $table->string('apple_event_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
