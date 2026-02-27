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
        if (!Schema::hasColumn('appointments', 'is_test')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->boolean('is_test')->default(false)->index();
            });
        }

        if (!Schema::hasColumn('appointments', 'test_tag')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('test_tag', 100)->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'test_tag')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex(['test_tag']);
                $table->dropColumn('test_tag');
            });
        }

        if (Schema::hasColumn('appointments', 'is_test')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex(['is_test']);
                $table->dropColumn('is_test');
            });
        }
    }
};
