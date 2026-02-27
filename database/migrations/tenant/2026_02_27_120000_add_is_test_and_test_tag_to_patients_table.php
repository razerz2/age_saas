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
        if (!Schema::hasColumn('patients', 'is_test')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->boolean('is_test')->default(false)->index();
            });
        }

        if (!Schema::hasColumn('patients', 'test_tag')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->string('test_tag', 100)->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('patients', 'test_tag')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropIndex(['test_tag']);
                $table->dropColumn('test_tag');
            });
        }

        if (Schema::hasColumn('patients', 'is_test')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropIndex(['is_test']);
                $table->dropColumn('is_test');
            });
        }
    }
};
