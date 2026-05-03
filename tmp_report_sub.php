<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$subId = 'a1afc65c-9372-4a04-81ce-10faea1947a7';
$sub = App\Models\Platform\Subscription::with('invoices')->find($subId);
if(!$sub){echo "sub not found\n"; exit;}
echo "sub_id={$sub->id}\n";
echo "payment_method={$sub->payment_method}\n";
echo "asaas_sub={$sub->asaas_subscription_id}\n";
echo "sync={$sub->asaas_sync_status}\n";
echo "status={$sub->status}\n";
foreach($sub->invoices as $i){
  echo "invoice={$i->id} provider_id={$i->provider_id} asaas={$i->asaas_payment_id} status={$i->status} pm={$i->payment_method} paid_at={$i->paid_at}\n";
}
