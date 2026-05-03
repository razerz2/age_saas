<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_payment_method_check");
DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_payment_method_check CHECK (payment_method IN ('PIX','PIX_RECURRENT','BOLETO','CREDIT_CARD','DEBIT_CARD'))");
echo "ok\n";
