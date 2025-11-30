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
        // A coluna já existe na migration original, apenas adiciona a foreign key
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('recurring_appointment_id')
                ->references('id')
                ->on('recurring_appointments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['recurring_appointment_id']);
            // Não remove a coluna pois ela faz parte da migration original
        });
    }
};

