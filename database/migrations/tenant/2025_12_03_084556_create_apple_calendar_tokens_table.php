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
        Schema::create('apple_calendar_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->unique()->constrained('doctors')->cascadeOnDelete();
            $table->string('username'); // Email do iCloud
            $table->text('password'); // Senha do app específica ou token
            $table->string('server_url')->default('https://caldav.icloud.com'); // URL do servidor CalDAV
            $table->string('calendar_url')->nullable(); // URL específica do calendário
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apple_calendar_tokens');
    }
};
