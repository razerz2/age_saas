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
    private const LOCK_TRIGGER = 'scheduled';

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

        $tenantTimezone = $this->resolveTenantTimezone($tenant);

        foreach ($campaigns as $campaign) {
            $stats['evaluated']++;

            $schedule = $this->resolveScheduleConfig($campaign, $tenantTimezone);
            if (!$schedule['valid']) {
                $stats['skipped_invalid']++;
                Log::info('campaign_automation_skip_invalid', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => false,
                    'reason' => (string) $schedule['reason'],
                ]));
                continue;
            }

            $eligibility = $this->evaluateScheduleEligibility($schedule);
            if (!$eligibility['eligible']) {
                $stats['skipped_schedule']++;
                Log::info('campaign_automation_skip_schedule', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => false,
                    'reason' => $eligibility['reason'],
                ]));
                continue;
            }

            $stats['eligible']++;

            try {
                $this->channelGate->assertChannelsEnabled($this->normalizeChannels($campaign->channels_json));
            } catch (DomainException $exception) {
                $stats['skipped_channels']++;
                Log::info('campaign_automation_skip_channels', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => true,
                    'reason' => 'channels_unavailable',
                    'message' => $exception->getMessage(),
                ]));
                continue;
            }

            if ($dryRun) {
                $stats['dry_run']++;
                Log::info('campaign_automation_dry_run', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => true,
                    'reason' => 'dry_run',
                ]));
                continue;
            }

            $lock = $this->acquireLock(
                campaignId: (int) $campaign->id,
                windowDate: (string) $schedule['window_date'],
                windowKey: (string) $schedule['window_key'],
                timezone: (string) $schedule['timezone'],
            );

            if (!$lock) {
                $stats['skipped_locked']++;
                Log::info('campaign_automation_skip_locked', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => true,
                    'reason' => 'already_locked',
                ]));
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
                            'schedule_mode' => $schedule['schedule_mode'],
                            'starts_at' => $schedule['starts_at']?->toIso8601String(),
                            'ends_at' => $schedule['ends_at']?->toIso8601String(),
                            'weekdays' => $schedule['weekdays'],
                            'times' => $schedule['times'],
                            'timezone' => $schedule['timezone'],
                            'window_key' => $schedule['window_key'],
                            'window_date' => $schedule['window_date'],
                            'local_now' => $schedule['local_now']->toIso8601String(),
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

                Log::info('campaign_automation_started', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => true,
                    'reason' => $result['created'] ? 'started' : 'already_running',
                    'run_id' => (int) $result['run']->id,
                    'created' => (bool) $result['created'],
                    'pending' => (int) data_get($result, 'totals.pending', 0),
                ]));
            } catch (Throwable $exception) {
                $stats['errors']++;
                $lock->status = self::LOCK_STATUS_ERROR;
                $lock->error_message = mb_substr($exception->getMessage(), 0, 500);
                $lock->save();

                Log::error('campaign_automation_start_failed', $this->buildLogContext($tenant, $campaign, $schedule, [
                    'eligibility' => true,
                    'reason' => 'start_failed',
                    'error' => $exception->getMessage(),
                ]));
            }
        }

        return $stats;
    }

    /**
     * @return array{
     *     valid:bool,
     *     reason:?string,
     *     schedule_mode:string,
     *     timezone:string,
     *     starts_at:?Carbon,
     *     ends_at:?Carbon,
     *     weekdays:array<int,int>,
     *     times:array<int,string>,
     *     local_now:Carbon,
     *     window_date:string,
     *     window_key:string
     * }
     */
    private function resolveScheduleConfig(Campaign $campaign, string $tenantTimezone): array
    {
        $timezone = $this->resolveCampaignTimezone($campaign, $tenantTimezone);
        $localNow = Carbon::now($timezone)->seconds(0);

        $scheduleMode = strtolower(trim((string) ($campaign->schedule_mode ?? 'period')));
        if (!in_array($scheduleMode, ['period', 'indefinite'], true)) {
            $scheduleMode = 'period';
        }

        $startsAt = $campaign->starts_at?->copy()->timezone($timezone);
        $endsAt = $campaign->ends_at?->copy()->timezone($timezone);

        $weekdays = $this->normalizeWeekdays($campaign->schedule_weekdays);
        if ($weekdays === []) {
            $weekdays = [0, 1, 2, 3, 4, 5, 6];
        }

        $times = $this->normalizeTimes($campaign->schedule_times);
        if ($times === []) {
            $legacyTime = trim((string) data_get($campaign->automation_json, 'schedule.time', ''));
            $times = $legacyTime !== '' ? $this->normalizeTimes([$legacyTime]) : [];
        }

        $schedule = [
            'valid' => true,
            'reason' => null,
            'schedule_mode' => $scheduleMode,
            'timezone' => $timezone,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'weekdays' => $weekdays,
            'times' => $times,
            'local_now' => $localNow,
            'window_date' => $localNow->format('Y-m-d'),
            'window_key' => $localNow->format('Y-m-d H:i'),
        ];

        if (!$startsAt) {
            $schedule['valid'] = false;
            $schedule['reason'] = 'missing_starts_at';
            return $schedule;
        }

        if ($scheduleMode === 'period' && !$endsAt) {
            $schedule['valid'] = false;
            $schedule['reason'] = 'missing_ends_at_for_period';
            return $schedule;
        }

        if ($endsAt && $endsAt->lt($startsAt)) {
            $schedule['valid'] = false;
            $schedule['reason'] = 'ends_at_before_starts_at';
            return $schedule;
        }

        if ($times === []) {
            $schedule['valid'] = false;
            $schedule['reason'] = 'missing_schedule_times';
            return $schedule;
        }

        return $schedule;
    }

    /**
     * @param array{
     *     valid:bool,
     *     reason:?string,
     *     schedule_mode:string,
     *     timezone:string,
     *     starts_at:?Carbon,
     *     ends_at:?Carbon,
     *     weekdays:array<int,int>,
     *     times:array<int,string>,
     *     local_now:Carbon,
     *     window_date:string,
     *     window_key:string
     * } $schedule
     * @return array{eligible:bool,reason:?string}
     */
    private function evaluateScheduleEligibility(array $schedule): array
    {
        /** @var Carbon $localNow */
        $localNow = $schedule['local_now'];
        /** @var ?Carbon $startsAt */
        $startsAt = $schedule['starts_at'];
        /** @var ?Carbon $endsAt */
        $endsAt = $schedule['ends_at'];
        $currentWeekday = (int) $localNow->dayOfWeek;
        $currentTime = $localNow->format('H:i');

        if ($startsAt && $localNow->lt($startsAt)) {
            return ['eligible' => false, 'reason' => 'starts_at_in_future'];
        }

        if ($schedule['schedule_mode'] === 'period' && $endsAt && $localNow->gt($endsAt)) {
            return ['eligible' => false, 'reason' => 'period_ended'];
        }

        if (!in_array($currentWeekday, $schedule['weekdays'], true)) {
            return ['eligible' => false, 'reason' => 'weekday_not_selected'];
        }

        if (!in_array($currentTime, $schedule['times'], true)) {
            return ['eligible' => false, 'reason' => 'time_not_selected'];
        }

        return ['eligible' => true, 'reason' => null];
    }

    private function acquireLock(int $campaignId, string $windowDate, string $windowKey, string $timezone): ?CampaignAutomationLock
    {
        try {
            return CampaignAutomationLock::query()->create([
                'campaign_id' => $campaignId,
                'trigger' => self::LOCK_TRIGGER,
                'window_date' => $windowDate,
                'window_key' => $windowKey,
                'timezone' => $timezone,
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

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'campaign_automation_locks_uq')
            || str_contains($message, 'campaign_automation_locks_campaign_window_uq');
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
     * @param mixed $weekdays
     * @return array<int,int>
     */
    private function normalizeWeekdays(mixed $weekdays): array
    {
        if (!is_array($weekdays)) {
            return [];
        }

        $normalized = [];
        foreach ($weekdays as $weekday) {
            if (!is_numeric($weekday)) {
                continue;
            }

            $day = (int) $weekday;
            if ($day < 0 || $day > 6 || in_array($day, $normalized, true)) {
                continue;
            }

            $normalized[] = $day;
        }

        sort($normalized);

        return $normalized;
    }

    /**
     * @param mixed $times
     * @return array<int,string>
     */
    private function normalizeTimes(mixed $times): array
    {
        if (!is_array($times)) {
            return [];
        }

        $normalized = [];
        foreach ($times as $time) {
            $value = $this->normalizeTime((string) $time);
            if ($value === null || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort($normalized);

        return $normalized;
    }

    private function normalizeTime(string $time): ?string
    {
        $time = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $time) !== 1) {
            return null;
        }

        [$hourRaw, $minuteRaw] = explode(':', $time, 2);
        $hour = (int) $hourRaw;
        $minute = (int) $minuteRaw;
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    private function resolveTenantTimezone(?PlatformTenant $tenant): string
    {
        $fallback = (string) config('app.timezone', 'America/Sao_Paulo');
        $timezone = '';

        if (function_exists('tenant_setting')) {
            $timezone = trim((string) tenant_setting('timezone', ''));
        }

        if ($timezone === '' && $tenant && isset($tenant->timezone)) {
            $timezone = trim((string) $tenant->timezone);
        }

        if ($timezone === '') {
            $timezone = $fallback;
        }

        if ($this->isValidTimezone($timezone)) {
            return $timezone;
        }

        return $fallback;
    }

    private function resolveCampaignTimezone(Campaign $campaign, string $tenantTimezone): string
    {
        $timezone = trim((string) ($campaign->timezone ?? ''));
        if ($timezone === '') {
            $timezone = trim((string) data_get($campaign->automation_json, 'timezone', ''));
        }
        if ($timezone === '') {
            $timezone = $tenantTimezone;
        }

        if ($this->isValidTimezone($timezone)) {
            return $timezone;
        }

        return $tenantTimezone;
    }

    private function isValidTimezone(string $timezone): bool
    {
        try {
            Carbon::now($timezone);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array{
     *     valid:bool,
     *     reason:?string,
     *     schedule_mode:string,
     *     timezone:string,
     *     starts_at:?Carbon,
     *     ends_at:?Carbon,
     *     weekdays:array<int,int>,
     *     times:array<int,string>,
     *     local_now:Carbon,
     *     window_date:string,
     *     window_key:string
     * } $schedule
     * @param array<string,mixed> $extra
     * @return array<string,mixed>
     */
    private function buildLogContext(?PlatformTenant $tenant, Campaign $campaign, array $schedule, array $extra = []): array
    {
        $context = [
            'tenant_id' => $tenant?->id,
            'tenant_subdomain' => $tenant?->subdomain,
            'campaign_id' => (int) $campaign->id,
            'window_key' => $schedule['window_key'],
            'timezone' => $schedule['timezone'],
            'schedule_mode' => $schedule['schedule_mode'],
            'now_local' => $schedule['local_now']->toIso8601String(),
            'now_weekday' => (int) $schedule['local_now']->dayOfWeek,
            'now_time' => $schedule['local_now']->format('H:i'),
            'starts_at' => $schedule['starts_at']?->toIso8601String(),
            'ends_at' => $schedule['ends_at']?->toIso8601String(),
            'weekdays' => $schedule['weekdays'],
            'times' => $schedule['times'],
        ];

        return array_merge($context, $extra);
    }
}
