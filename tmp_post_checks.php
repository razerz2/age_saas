<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$subId = 'a1afc65c-9372-4a04-81ce-10faea1947a7';
$sub = App\Models\Platform\Subscription::with('tenant','invoices')->find($subId);
if(!$sub){echo "sub not found\n";exit;}
$tenant = $sub->tenant;
echo "sub_status={$sub->status}\n";
echo "sub_sync={$sub->asaas_sync_status}\n";
echo "starts_at={$sub->starts_at} ends_at={$sub->ends_at} anchor={$sub->billing_anchor_date}\n";
echo "tenant_status={$tenant->status} suspended_at={$tenant->suspended_at}\n";
foreach($sub->invoices as $i){echo "invoice={$i->id} status={$i->status} paid_at={$i->paid_at} provider_id={$i->provider_id} asaas={$i->asaas_payment_id} pm={$i->payment_method}\n";}

$badInvoices = App\Models\Platform\Invoice::where('payment_method','PIX_RECURRENT')->where('provider_id','like','sub_%')->count();
echo "bad_pix_recurrent_sub_provider_count={$badInvoices}\n";
$badPay = App\Models\Platform\Invoice::where('payment_method','PIX_RECURRENT')->where(function($q){$q->where('provider_id','not like','pay_%')->orWhere('asaas_payment_id','not like','pay_%');})->count();
echo "bad_pix_recurrent_pay_prefix_count={$badPay}\n";
$badSub = App\Models\Platform\Subscription::where('payment_method','PIX_RECURRENT')->where('asaas_subscription_id','not like','sub_%')->count();
echo "bad_sub_prefix_count={$badSub}\n";
