<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Platform\Invoices::where('provider_id','pay_1w38txs5ct4lbhpa')->count()."\n";
