<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$badInvoices = App\Models\Platform\Invoices::where('payment_method','PIX_RECURRENT')->where('provider_id','like','sub_%')->count();
$badPay = App\Models\Platform\Invoices::where('payment_method','PIX_RECURRENT')->where(function($q){$q->where('provider_id','not like','pay_%')->orWhere('asaas_payment_id','not like','pay_%');})->count();
$badSub = App\Models\Platform\Subscription::where('payment_method','PIX_RECURRENT')->whereNotNull('asaas_subscription_id')->where('asaas_subscription_id','not like','sub_%')->count();
echo "bad_invoice_sub_prefix={$badInvoices}\n";
echo "bad_invoice_pay_prefix={$badPay}\n";
echo "bad_sub_prefix={$badSub}\n";

$pix = App\Models\Platform\Subscription::where('payment_method','PIX')->latest('created_at')->first();
$bol = App\Models\Platform\Subscription::where('payment_method','BOLETO')->latest('created_at')->first();
$cc = App\Models\Platform\Subscription::where('payment_method','CREDIT_CARD')->latest('created_at')->first();
if($pix){echo "pix_sub={$pix->id} status={$pix->status} auto_renew={$pix->auto_renew}\n";}
if($bol){echo "boleto_sub={$bol->id} status={$bol->status} auto_renew={$bol->auto_renew}\n";}
if($cc){echo "cc_sub={$cc->id} asaas_sub={$cc->asaas_subscription_id} status={$cc->status}\n";}
