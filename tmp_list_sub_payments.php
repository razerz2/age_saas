<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$cfg = asaas_config();
$subId = 'sub_pac2lb41a8b7auad';
$resp = Illuminate\Support\Facades\Http::withHeaders(['accept'=>'application/json','access_token'=>$cfg['api_key']])->get(rtrim($cfg['api_url'],'/').'/payments',['subscription'=>$subId,'limit'=>10]);
echo $resp->status()."\n";
echo $resp->body();
