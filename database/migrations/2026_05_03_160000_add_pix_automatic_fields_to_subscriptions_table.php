<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('asaas_pix_automatic_authorization_id')
                ->nullable()
                ->index('subscriptions_pix_auto_auth_id_idx');
            $table->string('asaas_pix_automatic_authorization_status')
                ->nullable()
                ->index('subscriptions_pix_auto_auth_status_idx');
            $table->string('asaas_pix_automatic_last_instruction_id')->nullable();
            $table->timestamp('asaas_pix_automatic_last_event_at')->nullable();
            $table->json('asaas_pix_automatic_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_pix_automatic_authorization_id',
                'asaas_pix_automatic_authorization_status',
                'asaas_pix_automatic_last_instruction_id',
                'asaas_pix_automatic_last_event_at',
                'asaas_pix_automatic_payload',
            ]);
        });
    }
};
