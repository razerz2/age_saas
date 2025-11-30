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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_full');
            $table->string('telefone');
            $table->string('email')->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_doctor')->default(false);
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->enum('role', ['admin', 'user', 'doctor'])->default('user')->after('status');
            $table->json('modules')->nullable();
            $table->uuid('tenant_id')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
