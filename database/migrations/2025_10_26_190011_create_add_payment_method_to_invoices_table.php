<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // 🔹 Adiciona o campo payment_method como ENUM
            $table->enum('payment_method', ['PIX', 'BOLETO', 'CREDIT_CARD', 'DEBIT_CARD'])
                  ->default('PIX')
                  ->after('payment_link');

        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
