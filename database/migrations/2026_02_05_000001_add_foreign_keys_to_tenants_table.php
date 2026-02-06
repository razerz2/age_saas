<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('pgsql')->table('tenants', function (Blueprint $table) {
            $table->foreign('network_id')
                ->references('id')
                ->on('clinic_networks')
                ->onDelete('set null');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->table('tenants', function (Blueprint $table) {
            $table->dropForeign(['network_id']);
            $table->dropForeign(['plan_id']);
        });
    }
};

