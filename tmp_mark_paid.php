<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$api = env('ASAAS_API_URL');
$key = env('ASAAS_API_KEY');
$pay = 'pay_1w38txs5ct4lbhpa';
$url = rtrim($api,'/') . "/payments/$pay/receiveInCash";
$resp = Illuminate\Support\Facades\Http::withHeaders(['access_token'=>$key])->post($url, [
  'paymentDate' => date('Y-m-d'),
  'value' => 159.00,
]);
echo "status=".$resp->status()."\n";
echo $resp->body()."\n";
