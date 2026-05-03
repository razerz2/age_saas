<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$api = rtrim(env('ASAAS_API_URL'), '/');
$key = env('ASAAS_API_KEY');
$sub = 'sub_pac2lb41a8b7auad';
$http = Illuminate\Support\Facades\Http::withHeaders(['access_token'=>$key]);
$r1 = $http->get("$api/subscriptions/$sub");
echo "sub_status=".$r1->status()."\n";
echo $r1->body()."\n\n";
$r2 = $http->get("$api/payments", ['subscription'=>$sub, 'limit'=>20]);
echo "payments_status=".$r2->status()."\n";
echo $r2->body()."\n";
