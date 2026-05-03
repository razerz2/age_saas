<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$api = rtrim(env('ASAAS_API_URL'), '/');
$key = env('ASAAS_API_KEY');
$webhookToken = env('ASAAS_WEBHOOK_SECRET');
$payId = 'pay_5rmgnpyqkcyt19n7';

$paymentResp = Illuminate\Support\Facades\Http::withHeaders(['access_token' => $key])->get("$api/payments/$payId");
$payment = $paymentResp->json();

$base = [
  'id' => 'evt_manual_' . uniqid(),
  'event' => 'PAYMENT_RECEIVED',
  'dateCreated' => date('Y-m-d H:i:s'),
  'account' => ['id' => 'sandbox-manual', 'ownerId' => null],
  'payment' => $payment,
];

$client = Illuminate\Support\Facades\Http::withHeaders([
  'Content-Type' => 'application/json',
  'Asaas-Access-Token' => $webhookToken,
]);

$r1 = $client->post('http://127.0.0.1:8000/webhook/asaas', $base);
echo "first_status={$r1->status()} body={$r1->body()}\n";

$r2 = $client->post('http://127.0.0.1:8000/webhook/asaas', $base);
echo "second_status={$r2->status()} body={$r2->body()}\n";
