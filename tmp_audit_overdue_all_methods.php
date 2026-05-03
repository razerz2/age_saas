<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$cases = [
  ['method' => 'PIX', 'invoice_id' => 'a1af2778-4dc0-4267-986b-f4f5f5178492', 'pay_id' => 'pay_i6lhfvtby3oyr4ml', 'sub_id' => 'a1af276d-9c3f-4102-a20b-27697d98e964'],
  ['method' => 'BOLETO', 'invoice_id' => 'a1afc759-148c-4d6b-bd50-7144920c5e26', 'pay_id' => 'pay_td2qk9ectxkxcfvw', 'sub_id' => 'a1ade1a2-8eb1-4b8c-b424-f2355f0dd457'],
  ['method' => 'CREDIT_CARD', 'invoice_id' => 'a1afc439-7210-4412-9d13-2601d05938b0', 'pay_id' => 'pay_n95ssqn73fon418k', 'sub_id' => 'a1af2730-8384-4189-a175-93084c3ad789'],
  ['method' => 'PIX_RECURRENT', 'invoice_id' => 'a1afdd2c-1a17-48b5-be6b-49e9badf89ac', 'pay_id' => 'pay_5rmgnpyqkcyt19n7', 'sub_id' => 'a1afc65c-9372-4a04-81ce-10faea1947a7'],
];

$api = rtrim(env('ASAAS_API_URL'), '/');
$key = env('ASAAS_API_KEY');
$token = env('ASAAS_WEBHOOK_SECRET');
$webhookUrl = 'http://127.0.0.1:8000/webhook/asaas';

$results = [];

foreach ($cases as $case) {
  $inv = DB::table('invoices')->where('id', $case['invoice_id'])->first();
  if (!$inv) {
    $results[] = ['method' => $case['method'], 'error' => 'invoice not found'];
    continue;
  }

  $tenantId = $inv->tenant_id;

  DB::table('invoices')->where('id', $case['invoice_id'])->update([
    'status' => 'pending',
    'paid_at' => null,
    'asaas_sync_status' => 'pending',
    'updated_at' => now(),
  ]);
  DB::table('subscriptions')->where('id', $case['sub_id'])->update([
    'status' => 'active',
    'asaas_sync_status' => 'pending',
    'updated_at' => now(),
  ]);
  DB::table('tenants')->where('id', $tenantId)->update([
    'status' => 'active',
    'suspended_at' => null,
    'asaas_sync_status' => 'pending',
    'updated_at' => now(),
  ]);

  $paymentResp = Http::withHeaders(['access_token' => $key])->get("$api/payments/{$case['pay_id']}");
  if (!$paymentResp->ok()) {
    $results[] = ['method' => $case['method'], 'error' => 'payment fetch failed', 'http' => $paymentResp->status()];
    continue;
  }

  $payment = $paymentResp->json();

  $payload = [
    'id' => 'evt_audit_overdue_' . strtolower($case['method']) . '_' . uniqid(),
    'event' => 'PAYMENT_OVERDUE',
    'dateCreated' => date('Y-m-d H:i:s'),
    'account' => ['id' => 'sandbox-audit', 'ownerId' => null],
    'payment' => $payment,
  ];

  $headers = ['Content-Type' => 'application/json', 'Asaas-Access-Token' => $token];

  $first = Http::withHeaders($headers)->post($webhookUrl, $payload);
  $after1 = [
    'invoice' => DB::table('invoices')->where('id', $case['invoice_id'])->first(['status','paid_at','asaas_sync_status']),
    'subscription' => DB::table('subscriptions')->where('id', $case['sub_id'])->first(['status','asaas_sync_status']),
    'tenant' => DB::table('tenants')->where('id', $tenantId)->first(['status','suspended_at','asaas_sync_status']),
  ];

  $second = Http::withHeaders($headers)->post($webhookUrl, $payload);
  $after2 = [
    'invoice' => DB::table('invoices')->where('id', $case['invoice_id'])->first(['status','paid_at','asaas_sync_status']),
    'subscription' => DB::table('subscriptions')->where('id', $case['sub_id'])->first(['status','asaas_sync_status']),
    'tenant' => DB::table('tenants')->where('id', $tenantId)->first(['status','suspended_at','asaas_sync_status']),
  ];

  $results[] = [
    'method' => $case['method'],
    'pay_id' => $case['pay_id'],
    'first_http' => $first->status(),
    'second_http' => $second->status(),
    'after_first' => $after1,
    'after_second' => $after2,
  ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
