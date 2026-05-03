<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$subId = 'a1afc65c-9372-4a04-81ce-10faea1947a7';
$tenantId = '45340052-01ec-4a19-b46c-b04cc5ad7914';
$invoiceId = 'a1afdd2c-1a17-48b5-be6b-49e9badf89ac';

DB::table('subscriptions')->where('id', $subId)->update([
  'status' => 'past_due',
  'ends_at' => '2026-06-02 00:00:00',
  'asaas_sync_status' => 'pending',
  'updated_at' => now(),
]);
DB::table('tenants')->where('id', $tenantId)->update([
  'status' => 'suspended',
  'suspended_at' => now(),
  'updated_at' => now(),
]);
DB::table('invoices')->where('id', $invoiceId)->update([
  'status' => 'pending',
  'paid_at' => null,
  'asaas_sync_status' => 'pending',
  'updated_at' => now(),
]);

echo "prepared\n";
