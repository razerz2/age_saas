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
        Schema::table('notification_templates', function (Blueprint $table): void {
            $table->string('scope', 20)->default('platform');
            $table->index(['scope', 'channel'], 'notification_templates_scope_channel_idx');

            $table->dropUnique('notification_templates_name_unique');
            $table->unique(
                ['scope', 'channel', 'name'],
                'notification_templates_scope_channel_name_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table): void {
            $table->dropUnique('notification_templates_scope_channel_name_unique');
            $table->dropIndex('notification_templates_scope_channel_idx');
            $table->unique('name', 'notification_templates_name_unique');
            $table->dropColumn('scope');
        });
    }
};

