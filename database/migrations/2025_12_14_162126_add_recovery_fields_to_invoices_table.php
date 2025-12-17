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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_recovery')->default(false)->after('status')->comment('Indica se é invoice de recovery');
            $table->uuid('recovery_origin_subscription_id')->nullable()->after('is_recovery')->comment('ID da subscription original que deu origem ao recovery');
            $table->uuid('recovery_target_subscription_id')->nullable()->after('recovery_origin_subscription_id')->comment('ID da subscription recovery_pending vinculada');
            $table->string('asaas_payment_link_id')->nullable()->after('asaas_payment_id')->comment('ID do payment link do Asaas (para recovery)');
            $table->string('asaas_recovery_subscription_id')->nullable()->after('asaas_payment_link_id')->comment('ID da nova assinatura criada no Asaas após pagamento do recovery');
            
            $table->foreign('recovery_origin_subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
            $table->foreign('recovery_target_subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['recovery_origin_subscription_id']);
            $table->dropForeign(['recovery_target_subscription_id']);
            $table->dropColumn([
                'is_recovery',
                'recovery_origin_subscription_id',
                'recovery_target_subscription_id',
                'asaas_payment_link_id',
                'asaas_recovery_subscription_id',
            ]);
        });
    }
};
