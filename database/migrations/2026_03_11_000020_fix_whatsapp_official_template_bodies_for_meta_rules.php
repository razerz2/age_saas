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
        if (!Schema::hasTable('whatsapp_official_templates')) {
            return;
        }

        // Chaves legadas de dominio clinico (tenant) que podem existir em ambientes antigos.
        $legacyBodies = [
            'appointment.pending_confirmation' => "Ola {{1}}.\n\nSeu agendamento esta pendente de confirmacao.\n\nData: {{2}}.\n\nLink para confirmacao: {{3}}.\n\nAguardamos sua confirmacao.",
            'appointment.confirmed' => "Ola {{1}}.\n\nSeu agendamento foi confirmado.\n\nData: {{2}}.\n\nSe precisar alterar ou cancelar, acesse: {{3}}.\n\nEstamos a disposicao.",
            'appointment.canceled' => "Ola {{1}}.\n\nSeu agendamento foi cancelado.\n\nData: {{2}}.\n\nPara realizar um novo agendamento, acesse: {{3}}.\n\nConte com a nossa equipe.",
            'appointment.expired' => "Ola {{1}}.\n\nO prazo para confirmacao do seu agendamento expirou.\n\nData prevista: {{2}}.\n\nPara realizar um novo agendamento, acesse: {{3}}.\n\nSe precisar de ajuda, fale conosco.",
            'waitlist.joined' => "Ola {{1}}.\n\nVoce foi incluido na lista de espera.\n\nAssim que surgir uma vaga, entraremos em contato.",
            'waitlist.offered' => "Ola {{1}}.\n\nUma vaga ficou disponivel para voce.\n\nData: {{2}}.\n\nConfirme sua participacao em: {{3}}.\n\nAguardamos sua resposta.",
        ];

        if (!$this->hasAnyTargetTemplateKey(array_keys($legacyBodies))) {
            return;
        }

        $this->updateBodies($legacyBodies);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('whatsapp_official_templates')) {
            return;
        }

        $legacyBodies = [
            'appointment.pending_confirmation' => "Ola {{1}}.\n\nSeu agendamento esta pendente de confirmacao.\n\nData: {{2}}\n\nConfirme seu agendamento pelo link:\n{{3}}",
            'appointment.confirmed' => "Ola {{1}}.\n\nSeu agendamento foi confirmado.\n\nData: {{2}}\n\nSe precisar alterar ou cancelar, utilize o link:\n{{3}}",
            'appointment.canceled' => "Ola {{1}}.\n\nSeu agendamento foi cancelado.\n\nData: {{2}}\n\nCaso deseje realizar um novo agendamento, acesse:\n{{3}}",
            'appointment.expired' => "Ola {{1}}.\n\nO prazo para confirmacao do seu agendamento expirou.\n\nData prevista: {{2}}\n\nRealize um novo agendamento pelo link:\n{{3}}",
            'waitlist.joined' => "Ola {{1}}.\n\nVoce foi incluido na lista de espera.\n\nAssim que surgir uma vaga entraremos em contato.",
            'waitlist.offered' => "Ola {{1}}.\n\nUma vaga ficou disponivel para voce.\n\nData: {{2}}\n\nConfirme sua participacao pelo link:\n{{3}}",
        ];

        if (!$this->hasAnyTargetTemplateKey(array_keys($legacyBodies))) {
            return;
        }

        $this->updateBodies($legacyBodies);
    }

    /**
     * @param array<string, string> $bodies
     */
    private function updateBodies(array $bodies): void
    {
        foreach ($bodies as $key => $bodyText) {
            DB::table('whatsapp_official_templates')
                ->where('provider', 'whatsapp_business')
                ->where('key', $key)
                ->where('version', 1)
                ->update([
                    'body_text' => $bodyText,
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
