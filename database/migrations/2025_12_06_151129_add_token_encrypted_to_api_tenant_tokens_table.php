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
        Schema::table('api_tenant_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('api_tenant_tokens', 'token_encrypted')) {
                $table->text('token_encrypted')->nullable()->after('token_hash');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_tenant_tokens', function (Blueprint $table) {
            $table->dropColumn('token_encrypted');
        });
    }
};
