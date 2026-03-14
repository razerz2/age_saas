<?php

namespace App\Console\Commands;

use App\Models\Platform\WhatsAppOfficialTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CleanupClinicalWhatsAppOfficialTemplatesCommand extends Command
{
    /**
     * @var array<int, string>
     */
    private const CLINICAL_KEYS = [
        'appointment.pending_confirmation',
        'appointment.confirmed',
        'appointment.canceled',
        'appointment.expired',
        'waitlist.joined',
        'waitlist.offered',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp-official-templates:clean-clinical
                            {--apply : Executa alteracoes no banco. Sem esta flag, o comando roda em dry-run}
                            {--mode=archive : Modo de limpeza: archive ou delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa templates clinicos legados do modulo Platform/whatsapp-official-templates (dry-run por padrao).';

    public function handle(): int
    {
        if (!Schema::hasTable('whatsapp_official_templates')) {
            $this->warn('Tabela whatsapp_official_templates nao encontrada. Nada a fazer.');
            return self::SUCCESS;
        }

        $mode = strtolower(trim((string) $this->option('mode')));
        if (!in_array($mode, ['archive', 'delete'], true)) {
            $this->error("Modo invalido: {$mode}. Use --mode=archive ou --mode=delete.");
            return self::INVALID;
        }

        $apply = (bool) $this->option('apply');
        $query = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->whereIn('key', self::CLINICAL_KEYS);

        /** @var Collection<int, WhatsAppOfficialTemplate> $rows */
        $rows = (clone $query)
            ->orderBy('key')
            ->orderByDesc('version')
            ->get();

        if ($rows->isEmpty()) {
            $this->info('Nenhum template clinico legado encontrado no provider oficial.');
            return self::SUCCESS;
        }

        $this->warn('Foram encontrados templates clinicos legados no modulo Platform.');
        $this->line('Keys alvo: ' . implode(', ', self::CLINICAL_KEYS));
        $this->newLine();

        $summaryByKey = $rows
            ->groupBy('key')
            ->map(static fn (Collection $group): int => $group->count())
            ->sortKeys();

        $this->table(
            ['Key', 'Quantidade'],
            $summaryByKey
                ->map(fn (int $count, string $key): array => [$key, $count])
                ->values()
                ->all()
        );

        $summaryByStatus = $rows
            ->groupBy('status')
            ->map(static fn (Collection $group): int => $group->count())
            ->sortKeys();

        $this->table(
            ['Status', 'Quantidade'],
            $summaryByStatus
                ->map(fn (int $count, string $status): array => [$status, $count])
                ->values()
                ->all()
        );

        $this->table(
            ['ID', 'Key', 'Versao', 'Status', 'Meta Name'],
            $rows
                ->take(20)
                ->map(static fn (WhatsAppOfficialTemplate $template): array => [
                    (string) $template->id,
                    (string) $template->key,
                    (string) $template->version,
                    (string) $template->status,
                    (string) $template->meta_template_name,
                ])
                ->values()
                ->all()
        );

        if ($rows->count() > 20) {
            $this->line('... ' . ($rows->count() - 20) . ' registro(s) adicionais omitidos na listagem.');
        }

        if (!$apply) {
            $this->newLine();
            $this->info('Dry-run concluido. Nenhum registro foi alterado.');
            $this->line('Para aplicar: php artisan whatsapp-official-templates:clean-clinical --apply --mode=' . $mode);
            return self::SUCCESS;
        }

        if ($mode === 'archive') {
            $toArchiveCount = (clone $query)
                ->where('status', '!=', WhatsAppOfficialTemplate::STATUS_ARCHIVED)
                ->count();

            $updated = (clone $query)
                ->where('status', '!=', WhatsAppOfficialTemplate::STATUS_ARCHIVED)
                ->update([
                    'status' => WhatsAppOfficialTemplate::STATUS_ARCHIVED,
                    'updated_at' => now(),
                ]);

            $alreadyArchived = $rows->count() - $toArchiveCount;

            $this->info("Execucao concluida (mode=archive). Templates arquivados: {$updated}. Ja arquivados: {$alreadyArchived}.");
            return self::SUCCESS;
        }

        $deleted = (clone $query)->delete();
        $this->info("Execucao concluida (mode=delete). Templates removidos: {$deleted}.");

        return self::SUCCESS;
    }
}
