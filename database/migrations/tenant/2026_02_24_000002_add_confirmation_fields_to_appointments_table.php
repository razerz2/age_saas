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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('confirmation_expires_at')->nullable()->after('ends_at');
            $table->dateTime('confirmed_at')->nullable()->after('confirmation_expires_at');
            $table->dateTime('canceled_at')->nullable()->after('confirmed_at');
            $table->dateTime('expired_at')->nullable()->after('canceled_at');
            $table->text('cancellation_reason')->nullable()->after('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'confirmation_expires_at',
                'confirmed_at',
                'canceled_at',
                'expired_at',
                'cancellation_reason',
            ]);
        });
    }
};

