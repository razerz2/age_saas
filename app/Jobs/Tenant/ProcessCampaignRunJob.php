<?php

namespace App\Jobs\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRecipient;
use App\Models\Tenant\CampaignRun;
use App\Services\Tenant\CampaignChannelGate;
use App\Services\Tenant\CampaignDeliveryService;
use App\Services\Tenant\NotificationDeliveryLogger;
use DomainException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class ProcessCampaignRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public array $backoff = [30, 120, 300];

    public function __construct(
        private readonly string $tenantId,
        private readonly int $campaignId,
        private readonly int $runId
    ) {
        $queue = (string) config('campaigns.queue', 'campaigns');
        if ($queue !== '') {
            $this->onQueue($queue);
        }
    }

    public function handle(
        CampaignChannelGate $gate,
        CampaignDeliveryService $deliveryService,
        NotificationDeliveryLogger $deliveryLogger
    ): void {
        $tenant = PlatformTenant::find($this->tenantId);
        if (!$tenant) {
            Log::warning('campaign_process_run_tenant_not_found', [
                'tenant_id' => $this->tenantId,
                'campaign_id' => $this->campaignId,
                'run_id' => $this->runId,
            ]);
            return;
        }

        $tenant->makeCurrent();

        $run = null;
        try {
            $campaign = Campaign::query()->find($this->campaignId);
            $run = CampaignRun::query()->find($this->runId);

            if (!$campaign || !$run) {
                Log::warning('campaign_process_run_not_found', [
                    'tenant_id' => $this->tenantId,
                    'campaign_id' => $this->campaignId,
                    'run_id' => $this->runId,
                ]);
                return;
            }

            if (strtolower((string) $run->status) !== 'running') {
                return;
            }

            if ($this->isPaused($campaign)) {
                $this->markAllPendingAsSkipped($run->id, 'Campanha pausada antes do processamento.');
                $this->refreshRunTotals($run, false);
                return;
            }

            $rateState = [];
            $stopProcessing = false;

            CampaignRecipient::query()
                ->where('campaign_run_id', $run->id)
                ->where('status', 'pending')
                ->orderBy('id')
                ->chunkById(100, function ($recipients) use (
                    &$campaign,
                    &$stopProcessing,
                    &$rateState,
                    $run,
                    $gate,
                    $deliveryService,
                    $deliveryLogger
                ) {
                    foreach ($recipients as $recipient) {
                        if ($this->isPaused($campaign->refresh())) {
                            $this->markAllPendingAsSkipped($run->id, 'Campanha pausada durante o processamento.');
                            $stopProcessing = true;
                            return false;
                        }

                        $current = CampaignRecipient::query()->find($recipient->id);
                        if (!$current || strtolower((string) $current->status) !== 'pending') {
                            continue;
                        }

                        try {
                            $gate->assertChannelsEnabled([(string) $current->channel]);
                        } catch (DomainException $e) {
                            $this->markRecipientError($current, $e->getMessage());
                            $this->logRecipientError($deliveryLogger, $campaign, $run, $current, $e->getMessage());
                            continue;
                        }

                        $this->applyRateLimit((string) $current->channel, $rateState);

                        $result = $deliveryService->sendRecipient($campaign, $run, $current);
                        if ($result['success']) {
                            $this->markRecipientSent($current);
                            continue;
                        }

                        $errorMessage = trim((string) ($result['error_message'] ?? 'Falha ao enviar destinatário da campanha.'));
                        $this->markRecipientError($current, $errorMessage);
                    }

                    return !$stopProcessing;
                });

            $this->refreshRunTotals($run, $stopProcessing);
        } catch (Throwable $e) {
            Log::error('campaign_process_run_failed', [
                'tenant_id' => $this->tenantId,
                'campaign_id' => $this->campaignId,
                'run_id' => $this->runId,
                'error' => $e->getMessage(),
            ]);

            if ($run instanceof CampaignRun) {
                $run->status = 'error';
                $run->error_message = mb_substr($e->getMessage(), 0, 500);
                $run->finished_at = now();
                $run->save();
            }

            throw $e;
        } finally {
            SpatieTenant::forgetCurrent();
        }
    }

    private function isPaused(Campaign $campaign): bool
    {
        return strtolower((string) $campaign->status) === 'paused';
    }

    private function markRecipientSent(CampaignRecipient $recipient): void
    {
        $recipient->status = 'sent';
        $recipient->sent_at = now();
        $recipient->error_message = null;
        $recipient->save();
    }

    private function markRecipientError(CampaignRecipient $recipient, string $errorMessage): void
    {
        $recipient->status = 'error';
        $recipient->error_message = mb_substr($errorMessage, 0, 500);
        $recipient->save();
    }

    private function markAllPendingAsSkipped(int $runId, string $message): void
    {
        CampaignRecipient::query()
            ->where('campaign_run_id', $runId)
            ->where('status', 'pending')
            ->update([
                'status' => 'skipped',
                'error_message' => mb_substr($message, 0, 500),
                'updated_at' => now(),
            ]);
    }

    /**
     * @param array<string,array{window_start:float,count:int}> $state
     */
    private function applyRateLimit(string $channel, array &$state): void
    {
        $channel = strtolower(trim($channel));
        $limit = match ($channel) {
            'email' => (int) config('campaigns.rate_limits.email_per_min', 60),
            'whatsapp' => (int) config('campaigns.rate_limits.whatsapp_per_min', 30),
            default => 0,
        };

        if ($limit <= 0) {
            return;
        }

        $now = microtime(true);
        if (!isset($state[$channel])) {
            $state[$channel] = [
                'window_start' => $now,
                'count' => 0,
            ];
        }

        $elapsed = $now - $state[$channel]['window_start'];
        if ($elapsed >= 60) {
            $state[$channel]['window_start'] = $now;
            $state[$channel]['count'] = 0;
        }

        if ($state[$channel]['count'] >= $limit) {
            $waitSeconds = max(0.0, 60 - $elapsed);
            if ($waitSeconds > 0) {
                usleep((int) min($waitSeconds * 1_000_000, 1_000_000));
            }
            $state[$channel]['window_start'] = microtime(true);
            $state[$channel]['count'] = 0;
        }

        $state[$channel]['count']++;
    }

    private function refreshRunTotals(CampaignRun $run, bool $stoppedByPause): void
    {
        $total = (int) CampaignRecipient::query()
            ->where('campaign_run_id', $run->id)
            ->count();

        $sent = (int) CampaignRecipient::query()
            ->where('campaign_run_id', $run->id)
            ->where('status', 'sent')
            ->count();

        $error = (int) CampaignRecipient::query()
            ->where('campaign_run_id', $run->id)
            ->where('status', 'error')
            ->count();

        $skipped = (int) CampaignRecipient::query()
            ->where('campaign_run_id', $run->id)
            ->where('status', 'skipped')
            ->count();

        $pending = (int) CampaignRecipient::query()
            ->where('campaign_run_id', $run->id)
            ->where('status', 'pending')
            ->count();

        $run->totals_json = [
            'total' => $total,
            'success' => $sent,
            'error' => $error,
            'skipped' => $skipped,
            'pending' => $pending,
        ];

        if ($pending === 0 || $stoppedByPause) {
            $run->status = 'finished';
            $run->finished_at = now();
            $run->error_message = null;
        } else {
            $run->status = 'running';
            $run->finished_at = null;
        }

        $run->save();
    }

    private function logRecipientError(
        NotificationDeliveryLogger $deliveryLogger,
        Campaign $campaign,
        CampaignRun $run,
        CampaignRecipient $recipient,
        string $message
    ): void {
        $tenantId = tenant()?->id;
        $tenantId = is_string($tenantId) ? trim($tenantId) : '';

        $error = new RuntimeException($message);
        $deliveryLogger->logError(
            $tenantId,
            (string) $recipient->channel,
            'campaign:' . (int) $campaign->id,
            (string) $recipient->channel,
            (string) $recipient->destination,
            null,
            'Campaign recipient skipped before provider dispatch.',
            $error,
            [
                'campaign_id' => (int) $campaign->id,
                'campaign_run_id' => (int) $run->id,
                'campaign_recipient_id' => (int) $recipient->id,
                'destination' => (string) $recipient->destination,
                'origin' => 'campaign_run',
                'channel' => (string) $recipient->channel,
            ]
        );
    }
}
