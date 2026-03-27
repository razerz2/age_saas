<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_official_templates')) {
            return;
        }

        Schema::table('whatsapp_official_templates', function (Blueprint $table): void {
            if (!Schema::hasColumn('whatsapp_official_templates', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'wa_official_templates_tenant_id_idx');
                $table->index(
                    ['tenant_id', 'provider', 'key', 'status'],
                    'wa_official_templates_tenant_provider_key_status_idx'
                );
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('whatsapp_official_templates')) {
            return;
        }

        Schema::table('whatsapp_official_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('whatsapp_official_templates', 'tenant_id')) {
                $table->dropIndex('wa_official_templates_tenant_provider_key_status_idx');
                $table->dropIndex('wa_official_templates_tenant_id_idx');
                $table->dropColumn('tenant_id');
            }
        });
    }
};

