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
        Schema::create('calendars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('name');
            $table->string('external_id')->nullable(); // google/apple
            $table->timestamps();
        });

        Schema::create('business_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->tinyInteger('weekday'); // 0=Dom, 6=Sáb
            $table->time('start_time');
            $table->time('end_time');
            $table->unique(['doctor_id', 'weekday', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
        Schema::dropIfExists('business_hours');
    }
};
