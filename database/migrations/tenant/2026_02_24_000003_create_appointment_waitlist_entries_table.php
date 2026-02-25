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
        Schema::create('appointment_waitlist_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignUuid('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 32);
            $table->string('offer_token')->nullable()->unique();
            $table->dateTime('offered_at')->nullable();
            $table->dateTime('offer_expires_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'doctor_id', 'starts_at', 'status'], 'waitlist_slot_status_idx');
            $table->unique(
                ['tenant_id', 'doctor_id', 'patient_id', 'starts_at', 'ends_at'],
                'waitlist_no_duplicate_patient_per_slot'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_waitlist_entries');
    }
};

