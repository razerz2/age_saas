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
            if (!Schema::hasColumn('appointments', 'queue_position')) {
                $table->unsignedInteger('queue_position')->nullable()->after('status');
                $table->index('queue_position');
            }

            if (!Schema::hasColumn('appointments', 'queue_updated_at')) {
                $table->timestamp('queue_updated_at')->nullable()->after('queue_position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'queue_position')) {
                $table->dropIndex('appointments_queue_position_index');
                $table->dropColumn('queue_position');
            }

            if (Schema::hasColumn('appointments', 'queue_updated_at')) {
                $table->dropColumn('queue_updated_at');
            }
        });
    }
};
