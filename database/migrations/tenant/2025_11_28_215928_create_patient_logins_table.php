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
        Schema::create('patient_logins', function (Blueprint $table) {
            $table->id();
            $table->uuid('patient_id');
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true)->after('email');
            $table->string('password');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_logins');
    }
};
