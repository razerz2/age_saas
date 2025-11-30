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
        Schema::create('recurring_appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignUuid('appointment_type_id')->nullable()->constrained('appointment_types');
            $table->date('start_date');
            $table->enum('end_type', ['none', 'total_sessions', 'date'])->default('none');
            $table->integer('total_sessions')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->enum('appointment_mode', ['presencial', 'online'])->default('presencial')->after('active');
            $table->text('google_recurring_event_ids')->nullable(); // Armazena JSON com os IDs dos eventos recorrentes criados no Google Calendar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_appointments');
    }
};

