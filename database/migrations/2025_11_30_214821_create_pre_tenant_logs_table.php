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
        Schema::create('pre_tenant_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('pre_tenant_id');
            $table->string('event');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('pre_tenant_id')->references('id')->on('pre_tenants')->onDelete('cascade');
            $table->index('pre_tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_tenant_logs');
    }
};
