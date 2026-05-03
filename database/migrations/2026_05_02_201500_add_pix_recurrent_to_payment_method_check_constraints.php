<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_payment_method_check");
        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_payment_method_check CHECK (payment_method IN ('PIX','PIX_RECURRENT','BOLETO','CREDIT_CARD','DEBIT_CARD'))");

        DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_payment_method_check");
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_payment_method_check CHECK (payment_method IN ('PIX','PIX_RECURRENT','BOLETO','CREDIT_CARD','DEBIT_CARD'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_payment_method_check");
        $subscriptionsHasPixRecurrent = (int) DB::table('subscriptions')
            ->where('payment_method', 'PIX_RECURRENT')
            ->count() > 0;
        DB::statement(
            $subscriptionsHasPixRecurrent
                ? "ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_payment_method_check CHECK (payment_method IN ('PIX','BOLETO','CREDIT_CARD','DEBIT_CARD')) NOT VALID"
                : "ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_payment_method_check CHECK (payment_method IN ('PIX','BOLETO','CREDIT_CARD','DEBIT_CARD'))"
        );

        DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_payment_method_check");
        $invoicesHasPixRecurrent = (int) DB::table('invoices')
            ->where('payment_method', 'PIX_RECURRENT')
            ->count() > 0;
        DB::statement(
            $invoicesHasPixRecurrent
                ? "ALTER TABLE invoices ADD CONSTRAINT invoices_payment_method_check CHECK (payment_method IN ('PIX','BOLETO','CREDIT_CARD','DEBIT_CARD')) NOT VALID"
                : "ALTER TABLE invoices ADD CONSTRAINT invoices_payment_method_check CHECK (payment_method IN ('PIX','BOLETO','CREDIT_CARD','DEBIT_CARD'))"
        );
    }
};
