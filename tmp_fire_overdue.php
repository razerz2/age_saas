<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$api = rtrim(env('ASAAS_API_URL'), '/');
$key = env('ASAAS_API_KEY');
$webhookToken = env('ASAAS_WEBHOOK_SECRET');
$payId = 'pay_5rmgnpyqkcyt19n7';
$payment = Illuminate\Support\Facades\Http::withHeaders(['access_token' => $key])->get("$api/payments/$payId")->json();
$payload = [
  'id' => 'evt_manual_' . uniqid(),
  'event' => 'PAYMENT_OVERDUE',
  'dateCreated' => date('Y-m-d H:i:s'),
  'account' => ['id' => 'sandbox-manual', 'ownerId' => null],
  'payment' => $payment,
];
$r = Illuminate\Support\Facades\Http::withHeaders([
  'Content-Type' => 'application/json',
  'Asaas-Access-Token' => $webhookToken,
])->post('http://127.0.0.1:8000/webhook/asaas', $payload);
echo "overdue_status={$r->status()} body={$r->body()}\n";
