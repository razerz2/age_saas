<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('tenant without subscription is not eligible for access', function () {
    $tenant = Tenant::factory()->create();

    expect($tenant->isEligibleForAccess())->toBeFalse();
});

test('tenant with plan_id but without subscription is not eligible for access', function () {
    $plan = Plan::factory()->create();
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

    expect($tenant->isEligibleForAccess())->toBeFalse();
});

test('current subscription plan uses active subscription as source of truth', function () {
    $legacyPlan = Plan::factory()->create();
    $commercialPlan = Plan::factory()->create();

    $tenant = Tenant::factory()->create(['plan_id' => $legacyPlan->id]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $commercialPlan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    expect($tenant->currentSubscriptionPlan()?->id)->toBe($commercialPlan->id)
        ->and($tenant->commercialPlan()?->id)->toBe($commercialPlan->id)
        ->and($tenant->preferredRegularizationPlanId())->toBe($commercialPlan->id);
});

test('preferred regularization plan keeps legacy fallback when there is no active subscription', function () {
    $legacyPlan = Plan::factory()->create();
    $tenant = Tenant::factory()->create(['plan_id' => $legacyPlan->id]);

    expect($tenant->currentSubscriptionPlan())->toBeNull()
        ->and($tenant->preferredRegularizationPlanId())->toBe($legacyPlan->id);
});

test('tenant with active subscription and plan is eligible for access', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    expect($tenant->isEligibleForAccess())->toBeTrue();
});

test('tenant with canceled subscription is not eligible for access', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'canceled',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    expect($tenant->isEligibleForAccess())->toBeFalse();
});

test('tenant with active commercial trial is eligible for access', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'is_active' => true,
    ]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'trialing',
        'is_trial' => true,
        'starts_at' => now()->subDay(),
        'trial_ends_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10),
        'auto_renew' => false,
    ]);

    expect($tenant->isEligibleForAccess())->toBeTrue()
        ->and($tenant->trialDaysRemaining())->toBeGreaterThanOrEqual(9);
});

test('subscription helper reports trial status and remaining days', function () {
    $subscription = Subscription::factory()->make([
        'status' => 'trialing',
        'is_trial' => true,
        'trial_ends_at' => now()->addDays(5),
    ]);

    expect($subscription->isTrialActive())->toBeTrue()
        ->and($subscription->isTrialExpired())->toBeFalse()
        ->and($subscription->daysRemainingInTrial())->toBeGreaterThanOrEqual(5);
});

test('subscription helper marks trial as expired when period has ended', function () {
    $subscription = Subscription::factory()->make([
        'status' => 'canceled',
        'is_trial' => true,
        'trial_ends_at' => now()->subDay(),
    ]);

    expect($subscription->isTrialActive())->toBeFalse()
        ->and($subscription->isTrialExpired())->toBeTrue()
        ->and($subscription->daysRemainingInTrial())->toBe(0);
});

test('tenant with expired trial is not eligible and is marked as trial expired', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create([
        'plan_type' => Plan::TYPE_REAL,
        'is_active' => true,
    ]);

    Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'canceled',
        'is_trial' => true,
        'starts_at' => now()->subDays(15),
        'trial_ends_at' => now()->subDay(),
        'ends_at' => now()->subDay(),
        'auto_renew' => false,
    ]);

    expect($tenant->isEligibleForAccess())->toBeFalse()
        ->and($tenant->commercialAccessStatusKey())->toBe('trial_expired')
        ->and($tenant->commercialAccessBlockedMessage())->toContain('expir');
});

test('tenant with active subscription sem plano relacionado is not eligible for access', function () {
    $tenant = new class extends Tenant {
        protected function resolvedActiveSubscription(): ?Subscription
        {
            $subscription = new Subscription();
            $subscription->setAttribute('id', 'fake-subscription');
            $subscription->setRelation('plan', null);

            return $subscription;
        }
    };

    expect($tenant->isEligibleForAccess())->toBeFalse();
});

test('tenant commercial status labels reflect no subscription and eligible scenarios', function () {
    $tenantWithoutSubscription = Tenant::factory()->create();

    expect($tenantWithoutSubscription->commercialAccessStatusKey())->toBe('no_subscription')
        ->and($tenantWithoutSubscription->commercialAccessStatusLabel())->toBe('Sem assinatura')
        ->and($tenantWithoutSubscription->commercialAccessSummaryLabel())->toBe('Bloqueada comercialmente');

    $tenantEligible = Tenant::factory()->create();
    $plan = Plan::factory()->create();

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenantEligible->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    $tenantEligible->load('activeSubscriptionRelation.plan');

    expect($tenantEligible->commercialAccessStatusKey())->toBe('eligible')
        ->and($tenantEligible->commercialAccessStatusLabel())->toBe('Apta para acesso')
        ->and($tenantEligible->commercialAccessSummaryLabel())->toBe('Apta para acesso')
        ->and($tenantEligible->subscriptionGrantsAccess($subscription))->toBeTrue();
});

test('tenant comercial status marks subscription without plan when relation is missing', function () {
    $tenant = new class extends Tenant {
        protected function resolvedActiveSubscription(): ?Subscription
        {
            $subscription = new Subscription();
            $subscription->setAttribute('id', 'fake-subscription-id');
            $subscription->setRelation('plan', null);

            return $subscription;
        }
    };

    expect($tenant->isEligibleForAccess())->toBeFalse()
        ->and($tenant->commercialAccessStatusKey())->toBe('subscription_without_plan')
        ->and($tenant->commercialAccessStatusLabel())->toBe('Assinatura sem plano')
        ->and($tenant->commercialAccessSummaryLabel())->toBe('Bloqueada comercialmente');
});
