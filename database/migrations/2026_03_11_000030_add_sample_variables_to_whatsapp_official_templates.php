<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_official_templates')) {
            return;
        }

        Schema::table('whatsapp_official_templates', function (Blueprint $table): void {
            if (!Schema::hasColumn('whatsapp_official_templates', 'sample_variables')) {
                $table->json('sample_variables')->nullable()->after('variables');
            }
        });

        // Chaves legadas de dominio clinico (tenant) que podem existir em ambientes antigos.
        $legacySamples = [
            'appointment.pending_confirmation' => [
                '1' => 'Rafael',
                '2' => '14/03/2026 as 09:00',
                '3' => 'https://app.allsync.com.br/agendamento/confirmar/abc123',
            ],
            'appointment.confirmed' => [
                '1' => 'Rafael',
                '2' => '14/03/2026 as 09:00',
                '3' => 'https://app.allsync.com.br/agendamento/gerenciar/abc123',
            ],
            'appointment.canceled' => [
                '1' => 'Rafael',
                '2' => '14/03/2026 as 09:00',
                '3' => 'https://app.allsync.com.br/agendamento/novo',
            ],
            'appointment.expired' => [
                '1' => 'Rafael',
                '2' => '14/03/2026 as 09:00',
                '3' => 'https://app.allsync.com.br/agendamento/novo',
            ],
            'waitlist.joined' => [
                '1' => 'Rafael',
            ],
            'waitlist.offered' => [
                '1' => 'Rafael',
                '2' => '14/03/2026 as 09:00',
                '3' => 'https://app.allsync.com.br/lista-espera/confirmar/abc123',
            ],
        ];

        if (!$this->hasAnyTargetTemplateKey(array_keys($legacySamples))) {
            return;
        }

        $this->updateSampleVariables($legacySamples);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('whatsapp_official_templates')) {
            return;
        }

        if (Schema::hasColumn('whatsapp_official_templates', 'sample_variables')) {
            Schema::table('whatsapp_official_templates', function (Blueprint $table): void {
                $table->dropColumn('sample_variables');
            });
        }
    }

    /**
     * @param array<string, array<string, string>> $samplesByKey
     */
    private function updateSampleVariables(array $samplesByKey): void
    {
        foreach ($samplesByKey as $key => $samples) {
            DB::table('whatsapp_official_templates')
                ->where('provider', 'whatsapp_business')
                ->where('key', $key)
                ->where('version', 1)
                ->update([
                    'sample_variables' => json_encode($samples, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * @param array<int, string> $keys
     */
    private function hasAnyTargetTemplateKey(array $keys): bool
    {
        return DB::table('whatsapp_official_templates')
            ->where('provider', 'whatsapp_business')
            ->whereIn('key', $keys)
            ->exists();
    }
};
