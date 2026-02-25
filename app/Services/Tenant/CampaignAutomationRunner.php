<?php

namespace App\Services\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignAutomationLock;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class CampaignAutomationRunner
{
    private const LOCK_STATUS_LOCKED = 'locked';
    private const LOCK_STATUS_DONE = 'done';
    private const LOCK_STATUS_ERROR = 'error';

    public function __construct(
        private readonly CampaignChannelGate $channelGate,
        private readonly CampaignStarter $campaignStarter
    ) {
    }

    /**
     * @return array{
     *     evaluated:int,
     *     eligible:int,
     *     started:int,
     *     dry_run:int,
     *     skipped_invalid:int,
     *     skipped_schedule:int,
     *     skipped_channels:int,
     *     skipped_locked:int,
     *     errors:int
     * }
     */
    public function runForTenant(?PlatformTenant $tenant = null, bool $dryRun = false): array
    {
        $stats = [
            'evaluated' => 0,
            'eligible' => 0,
            'started' => 0,
            'dry_run' => 0,
            'skipped_invalid' => 0,
            'skipped_schedule' => 0,
            'skipped_channels' => 0,
            'skipped_locked' => 0,
            'errors' => 0,
        ];

        $campaigns = Campaign::query()
            ->where('type', 'automated')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        foreach ($campaigns as $campaign) {
            $stats['evaluated']++;

            $automation = $this->resolveAutomationConfig($campaign);
            if ($automation === null) {
                $stats['skipped_invalid']++;
                continue;
            }

            $localNow = Carbon::now($automation['timezone']);
            if (!$this->isWithinScheduleWindow($localNow, $automation['schedule_time'])) {
                $stats['skipped_schedule']++;
                continue;
            }

            $stats['eligible']++;

            try {
                $this->channelGate->assertChannelsEnabled($this->normalizeChannels($campaign->channels_json));
            } catch (DomainException $exception) {
                $stats['skipped_channels']++;
                Log::warning('campaign_automation_channels_unavailable', [
                    'tenant_id' => $tenant?->id,
                    'campaign_id' => (int) $campaign->id,
                    'message' => $exception->getMessage(),
                ]);
                continue;
            }

            $windowDate = $localNow->toDateString();

            if ($dryRun) {
                $stats['dry_run']++;
                continue;
            }

            $lock = $this->acquireLock(
                campaignId: (int) $campaign->id,
                trigger: $automation['trigger'],
                windowDate: $windowDate
            );

            if (!$lock) {
                $stats['skipped_locked']++;
                continue;
            }

            try {
                $result = $this->campaignStarter->startCampaign(
                    campaign: $campaign,
                    initiatedBy: null,
                    trigger: 'automation',
                    dispatchProcessing: true,
                    contextOverrides: [
                        'trigger' => 'automation',
                        'initiated_by' => 'automation',
                        'automation' => [
                            'trigger' => $automation['trigger'],
                            'schedule_time' => $automation['schedule_time'],
                            'timezone' => $automation['timezone'],
                            'window_date' => $windowDate,
                            'local_now' => $localNow->toIso8601String(),
                        ],
                    ]
                );

                $lock->status = self::LOCK_STATUS_DONE;
                $lock->run_id = (int) $result['run']->id;
                $lock->error_message = null;
                $lock->save();

                if ($result['created']) {
                    $stats['started']++;
                }
            } catch (Throwable $exception) {
                $stats['errors']++;
                $lock->status = self::LOCK_STATUS_ERROR;
                $lock->error_message = mb_substr($exception->getMessage(), 0, 500);
                $lock->save();

                Log::error('campaign_automation_start_failed', [
                    'tenant_id' => $tenant?->id,
                    'campaign_id' => (int) $campaign->id,
                    'trigger' => $automation['trigger'],
                    'window_date' => $windowDate,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * @return array{trigger:string,schedule_time:string,timezone:string}|null
     */
    private function resolveAutomationConfig(Campaign $campaign): ?array
    {
        $automation = is_array($campaign->automation_json) ? $campaign->automation_json : [];

        $trigger = strtolower(trim((string) ($automation['trigger'] ?? '')));
        if (!in_array($trigger, ['birthday', 'inactive_patients'], true)) {
            return null;
        }

        $scheduleType = strtolower(trim((string) data_get($automation, 'schedule.type', '')));
        if ($scheduleType !== 'daily') {
            return null;
        }

        $scheduleTime = trim((string) data_get($automation, 'schedule.time', ''));
        if (!preg_match('/^\d{2}:\d{2}$/', $scheduleTime)) {
            return null;
        }

        $timezone = trim((string) ($automation['timezone'] ?? ''));
        if ($timezone === '') {
            $timezone = (string) config('campaigns.automation.default_timezone', 'America/Campo_Grande');
        }

        try {
            Carbon::now($timezone);
        } catch (Throwable) {
            return null;
        }

        return [
            'trigger' => $trigger,
            'schedule_time' => $scheduleTime,
            'timezone' => $timezone,
        ];
    }

    private function isWithinScheduleWindow(Carbon $localNow, string $scheduleTime): bool
    {
        [$hour, $minute] = array_pad(explode(':', $scheduleTime, 2), 2, '00');

        $scheduledAt = $localNow->copy()->setTime((int) $hour, (int) $minute, 0);
        $diffMinutes = abs($scheduledAt->diffInMinutes($localNow, false));

        return $diffMinutes <= max(1, (int) config('campaigns.automation.window_tolerance_minutes', 10));
    }

    private function acquireLock(int $campaignId, string $trigger, string $windowDate): ?CampaignAutomationLock
    {
        try {
            return CampaignAutomationLock::query()->create([
                'campaign_id' => $campaignId,
                'trigger' => $trigger,
                'window_date' => $windowDate,
                'status' => self::LOCK_STATUS_LOCKED,
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                return null;
            }

            throw $exception;
        }
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        if (in_array($sqlState, ['23000', '23505'], true)) {
            return true;
        }

        return str_contains(strtolower($exception->getMessage()), 'campaign_automation_locks_uq');
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
}
