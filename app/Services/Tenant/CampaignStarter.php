<?php

namespace App\Services\Tenant;

use App\Jobs\Tenant\ProcessCampaignRunJob;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRun;
use Illuminate\Support\Facades\DB;

class CampaignStarter
{
    public function __construct(
        private readonly CampaignAudienceBuilder $audienceBuilder
    ) {
    }

    /**
     * @return array{
     *     created:bool,
     *     run:CampaignRun,
     *     totals:array{total:int,success:int,error:int,skipped:int,pending:int},
     *     message:string
     * }
     */
    public function startCampaign(
        Campaign $campaign,
        ?int $initiatedBy = null,
        string $trigger = 'manual',
        bool $dispatchProcessing = true,
        array $contextOverrides = []
    ): array {
        $created = false;
        $run = null;
        $totals = [
            'total' => 0,
            'success' => 0,
            'error' => 0,
            'skipped' => 0,
            'pending' => 0,
        ];

        DB::connection('tenant')->transaction(function () use (
            $campaign,
            $initiatedBy,
            $trigger,
            $contextOverrides,
            &$created,
            &$run,
            &$totals
        ): void {
            $existingRun = CampaignRun::query()
                ->where('campaign_id', $campaign->id)
                ->where('status', 'running')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($existingRun) {
                $run = $existingRun;
                $totals = $this->normalizeTotals($existingRun->totals_json);
                $created = false;
                return;
            }

            $run = new CampaignRun();
            $run->campaign_id = $campaign->id;
            $run->status = 'running';
            $run->started_at = now();
            $context = [
                'trigger' => $trigger,
                'channels' => $this->normalizeChannels($campaign->channels_json),
                'audience_snapshot' => is_array($campaign->audience_json) ? $campaign->audience_json : [],
                'initiated_by' => $initiatedBy,
                'initiated_at' => now()->toIso8601String(),
            ];
            if ($contextOverrides !== []) {
                $context = array_replace_recursive($context, $contextOverrides);
            }

            $run->context_json = $context;
            $run->totals_json = $totals;
            $run->save();

            $created = true;

            $audience = $this->audienceBuilder->build($campaign, [
                'trigger' => $trigger,
                'context' => $context,
            ]);
            $recipientRows = $this->buildRecipientRows($campaign, $run, $audience);

            if ($recipientRows !== []) {
                foreach (array_chunk($recipientRows, 500) as $chunk) {
                    DB::connection('tenant')
                        ->table('campaign_recipients')
                        ->insertOrIgnore($chunk);
                }
            }

            $pendingCount = (int) DB::connection('tenant')
                ->table('campaign_recipients')
                ->where('campaign_run_id', $run->id)
                ->where('status', 'pending')
                ->count();

            $totals = [
                'total' => $pendingCount,
                'success' => 0,
                'error' => 0,
                'skipped' => 0,
                'pending' => $pendingCount,
            ];

            $run->totals_json = $totals;
            if ($pendingCount === 0) {
                $run->status = 'finished';
                $run->finished_at = now();
            }
            $run->save();
        });

        if ($run === null) {
            throw new \RuntimeException('Não foi possível inicializar a execução da campanha.');
        }

        if ($created && $dispatchProcessing && (int) ($totals['pending'] ?? 0) > 0) {
            $tenantId = tenant()?->id;
            $tenantId = is_string($tenantId) ? trim($tenantId) : '';

            if ($tenantId !== '') {
                $queue = (string) config('campaigns.queue', 'campaigns');
                $pendingDispatch = ProcessCampaignRunJob::dispatch($tenantId, (int) $campaign->id, (int) $run->id);
                if ($queue !== '' && method_exists($pendingDispatch, 'onQueue')) {
                    $pendingDispatch->onQueue($queue);
                }
                if (method_exists($pendingDispatch, 'afterCommit')) {
                    $pendingDispatch->afterCommit();
                }
            }
        }

        return [
            'created' => $created,
            'run' => $run,
            'totals' => $totals,
            'message' => $created
                ? 'Execução iniciada com sucesso.'
                : 'Já existe execução em andamento para esta campanha.',
        ];
    }

    /**
     * @param mixed $totals
     * @return array{total:int,success:int,error:int,skipped:int,pending:int}
     */
    private function normalizeTotals(mixed $totals): array
    {
        $totals = is_array($totals) ? $totals : [];

        return [
            'total' => (int) ($totals['total'] ?? 0),
            'success' => (int) ($totals['success'] ?? 0),
            'error' => (int) ($totals['error'] ?? 0),
            'skipped' => (int) ($totals['skipped'] ?? 0),
            'pending' => (int) ($totals['pending'] ?? 0),
        ];
    }

    /**
     * @param mixed $channels
     * @return array<int,string>
     */
    private function normalizeChannels(mixed $channels): array
    {
        if (!is_array($channels)) {
            return [];
        }

        $normalized = [];
        foreach ($channels as $channel) {
            $value = strtolower(trim((string) $channel));
            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            if (in_array($value, ['email', 'whatsapp'], true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<int,array{
     *     target_type:string,
     *     target_id:?int,
     *     email:?string,
     *     phone:?string,
     *     vars_json:array<string,mixed>
     * }> $audience
     * @return array<int,array<string,mixed>>
     */
    private function buildRecipientRows(Campaign $campaign, CampaignRun $run, array $audience): array
    {
        $channels = $this->normalizeChannels($campaign->channels_json);
        if ($channels === []) {
            return [];
        }

        $rows = [];
        $uniqueKeys = [];
        $now = now();

        foreach ($audience as $entry) {
            foreach ($channels as $channel) {
                $destination = $channel === 'email'
                    ? $this->normalizeEmail($entry['email'] ?? null)
                    : $this->normalizePhone($entry['phone'] ?? null);

                if ($destination === null) {
                    continue;
                }

                $uniqueKey = $run->id . '|' . $channel . '|' . strtolower($destination);
                if (isset($uniqueKeys[$uniqueKey])) {
                    continue;
                }
                $uniqueKeys[$uniqueKey] = true;

                $rows[] = [
                    'campaign_id' => (int) $campaign->id,
                    'campaign_run_id' => (int) $run->id,
                    'target_type' => (string) ($entry['target_type'] ?? 'patient'),
                    'target_id' => $entry['target_id'] ?? null,
                    'channel' => $channel,
                    'destination' => $destination,
                    'status' => 'pending',
                    'sent_at' => null,
                    'error_message' => null,
                    'vars_json' => json_encode($entry['vars_json'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'meta_json' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $rows;
    }

    private function normalizeEmail(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $email = trim((string) $value);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $email;
    }

    private function normalizePhone(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $phone = trim((string) $value);
        return $phone !== '' ? $phone : null;
    }
}
