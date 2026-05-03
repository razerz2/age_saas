<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = App\Models\Platform\Invoices::orderByDesc('created_at')->limit(8)->get(['id','subscription_id','payment_method','provider_id','status','created_at']);
foreach($rows as $r){echo "{$r->created_at} | {$r->id} | {$r->payment_method} | {$r->provider_id} | {$r->status}\n";}
