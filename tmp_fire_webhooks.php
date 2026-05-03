<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$api = env('ASAAS_API_URL');
$key = env('ASAAS_API_KEY');
$webhookToken = env('ASAAS_WEBHOOK_SECRET');
$payId = 'pay_1w38txs5ct4lbhpa';
$paymentResp = Illuminate\Support\Facades\Http::withHeaders(['access_token'=>$key])->get(rtrim($api,'/') . "/payments/$payId");
$payment = $paymentResp->json();
$base = [
  'id' => 'evt_manual_' . uniqid(),
  'dateCreated' => date('Y-m-d H:i:s'),
  'account' => ['id' => 'sandbox-manual', 'ownerId' => null],
  'payment' => $payment,
];
$client = Illuminate\Support\Facades\Http::withHeaders([
  'Content-Type' => 'application/json',
  'Asaas-Access-Token' => $webhookToken,
]);
$payload1 = $base; $payload1['event'] = 'PAYMENT_RECEIVED';
$r1 = $client->post('http://127.0.0.1:8000/webhook/asaas', $payload1);
echo "received1_status={$r1->status()} body={$r1->body()}\n";
$r2 = $client->post('http://127.0.0.1:8000/webhook/asaas', $payload1);
echo "received2_status={$r2->status()} body={$r2->body()}\n";
$payload2 = $base; $payload2['id'] = 'evt_manual_' . uniqid(); $payload2['event'] = 'PAYMENT_OVERDUE';
$r3 = $client->post('http://127.0.0.1:8000/webhook/asaas', $payload2);
echo "overdue_status={$r3->status()} body={$r3->body()}\n";
