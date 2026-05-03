<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Platform\Tenant::find('45340052-01ec-4a19-b46c-b04cc5ad7914');
$plan = App\Models\Platform\Plan::find('a1ade10e-4f21-40c7-95c6-43f272fa56fa');

$sub = App\Models\Platform\Subscription::create([
    'tenant_id' => $tenant->id,
    'plan_id' => $plan->id,
    'starts_at' => now(),
    'ends_at' => now()->addMonths(1),
    'due_day' => 5,
    'billing_anchor_date' => now()->toDateString(),
    'status' => 'pending',
    'auto_renew' => true,
    'payment_method' => 'PIX_RECURRENT',
    'is_trial' => false,
]);

$ok = app(App\Http\Controllers\Platform\SubscriptionController::class)->syncWithAsaas($sub->fresh(), true);
$sub = $sub->fresh();
$inv = App\Models\Platform\Invoices::where('subscription_id', $sub->id)->latest('created_at')->first();

echo json_encode([
    'ok' => $ok,
    'subscription_id' => $sub->id,
    'payment_method' => $sub->payment_method,
    'asaas_subscription_id' => $sub->asaas_subscription_id,
    'asaas_synced' => $sub->asaas_synced,
    'asaas_sync_status' => $sub->asaas_sync_status,
    'status' => $sub->status,
    'invoice' => $inv ? $inv->only(['id','provider_id','asaas_payment_id','payment_method','status','payment_link']) : null,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
