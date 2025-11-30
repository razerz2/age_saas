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
        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('crm_number')->nullable();
            $table->string('crm_state')->nullable();
            $table->string('signature')->nullable();
            $table->string('label_singular', 50)->nullable()->after('signature');
            $table->string('label_plural', 50)->nullable()->after('label_singular');
            $table->string('registration_label', 50)->nullable()->after('label_plural');
            $table->string('registration_value', 100)->nullable()->after('registration_label');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
