<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = DB::select("select conname, pg_get_constraintdef(c.oid) as def, c.convalidated from pg_constraint c where conname in ('subscriptions_payment_method_check','invoices_payment_method_check') order by conname");
foreach($rows as $r){echo "{$r->conname} | {$r->def} | validated=".($r->convalidated?'true':'false')."\n";}
