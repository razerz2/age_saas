<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $feature = DB::table('subscription_features')
            ->where('name', 'whatsapp_bot')
            ->first();

        if (!$feature) {
            $featureId = (string) Str::uuid();

            DB::table('subscription_features')->insert([
                'id' => $featureId,
                'name' => 'whatsapp_bot',
                'label' => 'Bot de WhatsApp',
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $featureId = (string) $feature->id;
        }

        $planRuleIds = DB::table('plan_access_rules')->pluck('id');

        foreach ($planRuleIds as $planRuleId) {
            $whatsappNotificationsAllowed = DB::table('plan_access_rule_feature as paf')
                ->join('subscription_features as sf', 'sf.id', '=', 'paf.feature_id')
                ->where('paf.plan_access_rule_id', $planRuleId)
                ->where('sf.name', 'whatsapp_notifications')
                ->value('paf.allowed');

            DB::table('plan_access_rule_feature')->updateOrInsert(
                [
                    'plan_access_rule_id' => $planRuleId,
                    'feature_id' => $featureId,
                ],
                [
                    'allowed' => (bool) ($whatsappNotificationsAllowed ?? false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $feature = DB::table('subscription_features')
            ->where('name', 'whatsapp_bot')
            ->first();

        if (!$feature) {
            return;
        }

        DB::table('plan_access_rule_feature')
            ->where('feature_id', $feature->id)
            ->delete();

        DB::table('subscription_features')
            ->where('id', $feature->id)
            ->delete();
    }
};
