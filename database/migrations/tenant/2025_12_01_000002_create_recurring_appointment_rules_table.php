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
        Schema::create('recurring_appointment_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recurring_appointment_id')->constrained('recurring_appointments')->cascadeOnDelete();
            $table->enum('weekday', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly'])->default('weekly');
            $table->integer('interval')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_appointment_rules');
    }
};

