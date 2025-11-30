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
        Schema::create('online_appointment_instructions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('meeting_link')->nullable();
            $table->string('meeting_app')->nullable();
            $table->text('general_instructions')->nullable();
            $table->text('patient_instructions')->nullable();
            $table->timestamp('sent_by_email_at')->nullable();
            $table->timestamp('sent_by_whatsapp_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_appointment_instructions');
    }
};

