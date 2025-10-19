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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('context')->nullable(); // subscription, invoice, etc.
            $table->enum('level', ['info', 'warning', 'error'])->default('info');
            $table->enum('status', ['new', 'read'])->default('new');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
