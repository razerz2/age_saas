<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$api = rtrim(env('ASAAS_API_URL'), '/');
$key = env('ASAAS_API_KEY');
$pay = 'pay_5rmgnpyqkcyt19n7';
$url = "$api/payments/$pay/receiveInCash";
$r = Illuminate\Support\Facades\Http::withHeaders(['access_token'=>$key])->post($url, [
  'paymentDate' => date('Y-m-d'),
  'value' => 159.00,
]);
echo "status={$r->status()}\n";
echo $r->body()."\n";
