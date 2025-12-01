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
        Schema::create('tenant_plan_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('max_admin_users')->default(0);
            $table->integer('max_common_users')->default(0);
            $table->integer('max_doctors')->default(0);
            $table->jsonb('allowed_features')->default(json_encode([]));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_plan_limits');
    }
};
