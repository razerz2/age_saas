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
        Schema::create('pre_tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('fantasy_name')->nullable();
            $table->string('email');
            $table->string('document')->nullable();
            $table->string('phone')->nullable();
            $table->uuid('plan_id')->nullable();
            $table->enum('status', ['pending', 'paid', 'canceled'])->default('pending');
            $table->string('asaas_customer_id')->nullable();
            $table->string('asaas_payment_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('subdomain_suggested')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
            $table->foreign('country_id')->references('id_pais')->on('paises')->onDelete('set null');
            $table->foreign('state_id')->references('id_estado')->on('estados')->onDelete('set null');
            $table->foreign('city_id')->references('id_cidade')->on('cidades')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_tenants');
    }
};
