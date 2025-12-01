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
        Schema::create('plan_access_rule_feature', function (Blueprint $table) {
            $table->uuid('plan_access_rule_id');
            $table->uuid('feature_id');
            $table->boolean('allowed')->default(false);
            $table->timestamps();

            $table->primary(['plan_access_rule_id', 'feature_id']);
            $table->foreign('plan_access_rule_id')->references('id')->on('plan_access_rules')->onDelete('cascade');
            $table->foreign('feature_id')->references('id')->on('subscription_features')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_access_rule_feature');
    }
};
