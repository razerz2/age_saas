<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_charges', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('due_date');
            $table->string('payment_method')->nullable()->after('paid_at'); // pix, credit_card, boleto, debit_card
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_charges', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'payment_method']);
        });
    }
};

