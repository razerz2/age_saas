<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_hours', function (Blueprint $table) {
            $table->dropUnique('business_hours_doctor_id_weekday_start_time_end_time_unique');
            $table->unique(['doctor_id', 'weekday'], 'business_hours_doctor_id_weekday_unique');
        });
    }

    public function down(): void
    {
        Schema::table('business_hours', function (Blueprint $table) {
            $table->dropUnique('business_hours_doctor_id_weekday_unique');
            $table->unique(['doctor_id', 'weekday', 'start_time', 'end_time'], 'business_hours_doctor_id_weekday_start_time_end_time_unique');
        });
    }
};