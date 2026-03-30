<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $allowedOrigins = [
            'public',
            'portal',
            'internal',
            'whatsapp_bot',
        ];

        $quotedOrigins = implode(', ', array_map(static fn (string $origin): string => "'" . $origin . "'", $allowedOrigins));

        DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_origin_check');
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_origin_check CHECK (origin IN ({$quotedOrigins}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $legacyOrigins = [
            'public',
            'portal',
            'internal',
        ];

        $quotedOrigins = implode(', ', array_map(static fn (string $origin): string => "'" . $origin . "'", $legacyOrigins));

        DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_origin_check');
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_origin_check CHECK (origin IN ({$quotedOrigins}))");
    }
};

