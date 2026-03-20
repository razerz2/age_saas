<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platform\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $startsAt = now();

        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMonth(),
            'due_day' => 1,
            'billing_anchor_date' => $startsAt->toDateString(),
            'recovery_started_at' => null,
            'status' => 'active',
            'auto_renew' => true,
            'payment_method' => 'PIX',
            'is_trial' => false,
            'trial_ends_at' => null,
            'asaas_subscription_id' => null,
            'asaas_synced' => false,
            'asaas_sync_status' => 'pending',
            'asaas_last_sync_at' => null,
            'asaas_last_error' => null,
        ];
    }
}
