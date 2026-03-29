<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_whatsapp_global_instances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('provider', 40);
            $table->string('instance_name', 120);
            $table->boolean('managed_by_system')->default(true);
            $table->string('status', 40)->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unique(
                ['tenant_id', 'provider'],
                'tenant_whatsapp_global_instances_tenant_provider_unq'
            );

            $table->unique(
                ['provider', 'instance_name'],
                'tenant_whatsapp_global_instances_provider_instance_unq'
            );

            $table->index(['provider', 'status'], 'tenant_whatsapp_global_instances_provider_status_idx');
            $table->index(['tenant_id', 'status'], 'tenant_whatsapp_global_instances_tenant_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_whatsapp_global_instances');
    }
};
