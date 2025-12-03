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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('admin_login_url')->nullable()->after('subdomain');
            $table->string('admin_email')->nullable()->after('admin_login_url');
            $table->string('admin_password')->nullable()->after('admin_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['admin_login_url', 'admin_email', 'admin_password']);
        });
    }
};
