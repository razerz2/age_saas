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
        Schema::table('patient_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('pais_id')->nullable()->after('country');
            $table->unsignedBigInteger('estado_id')->nullable()->after('state');
            $table->unsignedBigInteger('cidade_id')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_addresses', function (Blueprint $table) {
            $table->dropColumn(['pais_id', 'estado_id', 'cidade_id']);
        });
    }
};
