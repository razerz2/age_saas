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
        Schema::connection('pgsql')->create('network_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_network_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->enum('role', ['admin', 'viewer', 'finance'])->default('viewer');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('clinic_network_id')
                ->references('id')
                ->on('clinic_networks')
                ->onDelete('cascade');

            $table->index('clinic_network_id');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_users');
    }
};
