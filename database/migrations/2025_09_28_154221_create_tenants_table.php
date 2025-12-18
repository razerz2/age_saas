<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('document', 30)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('subdomain')->unique();
            $table->string('admin_login_url')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_password')->nullable();
            $table->string('db_host');
            $table->integer('db_port')->default(5432);
            $table->string('db_name')->unique();
            $table->string('db_username');
            $table->string('db_password');
            $table->uuid('network_id')->nullable();
            $table->uuid('plan_id')->nullable();
            $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
            $table->timestamp('suspended_at')->nullable()->comment('Data em que o tenant foi suspenso');
            $table->timestamp('canceled_at')->nullable()->comment('Data em que o tenant foi cancelado');
            $table->date('trial_ends_at')->nullable();
            $table->string('asaas_customer_id')->nullable();
            $table->boolean('asaas_synced')->default(false);
            $table->string('asaas_sync_status')->default('pending');
            $table->timestamp('asaas_last_sync_at')->nullable();
            $table->text('asaas_last_error')->nullable();
            $table->timestamps();

            $table->foreign('network_id')->references('id')->on('clinic_networks')->onDelete('set null');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
            $table->index('network_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
