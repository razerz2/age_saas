<?php

namespace App\Services\Tenant\Scheduling;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DoctorSlotFinder
{
    private const OCCUPIED_STATUSES = [
        'scheduled',
        'rescheduled',
        'pending_confirmation',
        'confirmed',
        'arrived',
        'in_service',
    ];

    private string $timezone;

    public function __construct()
    {
        $this->timezone = (string) config('app.timezone', 'America/Campo_Grande');
    }

    /**
     * @param  array<int, array{starts_at:mixed, ends_at:mixed}>  $reservedIntervals
     * @return Collection<int, array{calendar_id:string, starts_at:CarbonImmutable, ends_at:CarbonImmutable}>
     */
    public function findAvailableSlots(
        string $doctorId,
        CarbonInterface|string $date,
        int $durationMinutes,
        array $reservedIntervals = []
    ): Collection {
        $targetDate = $this->normalizeDate($date);
        $duration = max(5, $durationMinutes);
        $calendarId = Calendar::query()->where('doctor_id', $doctorId)->value('id');
        if (!$calendarId) {
            return collect();
        }

        $businessHours = BusinessHour::query()
            ->where('doctor_id', $doctorId)
            ->where('weekday', $targetDate->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($businessHours->isEmpty()) {
            return collect();
        }

        $occupiedIntervals = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('starts_at', $targetDate->toDateString())
            ->whereIn('status', self::OCCUPIED_STATUSES)
            ->get(['starts_at', 'ends_at'])
            ->map(function (Appointment $appointment): array {
                return [
                    'starts_at' => CarbonImmutable::parse($appointment->starts_at, $this->timezone)->startOfMinute(),
                    'ends_at' => CarbonImmutable::parse($appointment->ends_at, $this->timezone)->startOfMinute(),
                ];
            });

        $recurringBlocks = $this->resolveRecurringBlocks($doctorId, $targetDate);
        $reservedBlocks = collect($reservedIntervals)->map(function (array $interval): array {
            return [
                'starts_at' => CarbonImmutable::parse((string) $interval['starts_at'], $this->timezone)->startOfMinute(),
                'ends_at' => CarbonImmutable::parse((string) $interval['ends_at'], $this->timezone)->startOfMinute(),
            ];
        });

        $slots = collect();

        foreach ($businessHours as $businessHour) {
            $windowStart = CarbonImmutable::parse(
                $targetDate->toDateString() . ' ' . $this->normalizeTime((string) $businessHour->start_time),
                $this->timezone
            )->startOfMinute();

            $windowEnd = CarbonImmutable::parse(
                $targetDate->toDateString() . ' ' . $this->normalizeTime((string) $businessHour->end_time),
                $this->timezone
            )->startOfMinute();

            $breakStart = null;
            $breakEnd = null;
            if ($businessHour->break_start_time && $businessHour->break_end_time) {
                $breakStart = CarbonImmutable::parse(
                    $targetDate->toDateString() . ' ' . $this->normalizeTime((string) $businessHour->break_start_time),
                    $this->timezone
                )->startOfMinute();

                $breakEnd = CarbonImmutable::parse(
                    $targetDate->toDateString() . ' ' . $this->normalizeTime((string) $businessHour->break_end_time),
                    $this->timezone
                )->startOfMinute();
            }

            $cursor = $windowStart;
            while ($cursor->addMinutes($duration)->lte($windowEnd)) {
                $slotStart = $cursor;
                $slotEnd = $cursor->addMinutes($duration);
                $cursor = $cursor->addMinutes($duration);

                if ($breakStart && $breakEnd && $this->intervalsOverlap($slotStart, $slotEnd, $breakStart, $breakEnd)) {
                    continue;
                }

                if ($this->hasConflict($slotStart, $slotEnd, $occupiedIntervals)) {
                    continue;
                }

                if ($this->hasConflict($slotStart, $slotEnd, $recurringBlocks)) {
                    continue;
                }

                if ($this->hasConflict($slotStart, $slotEnd, $reservedBlocks)) {
                    continue;
                }

                $slots->push([
                    'calendar_id' => (string) $calendarId,
                    'starts_at' => $slotStart,
                    'ends_at' => $slotEnd,
                ]);
            }
        }

        return $slots;
    }

    /**
     * @return Collection<int, array{starts_at:CarbonImmutable, ends_at:CarbonImmutable}>
     */
    private function resolveRecurringBlocks(string $doctorId, CarbonImmutable $date): Collection
    {
        $weekday = RecurringAppointmentRule::weekdayFromNumber($date->dayOfWeek);

        $recurrings = RecurringAppointment::query()
            ->with(['rules' => function ($query) use ($weekday): void {
                $query->where('weekday', $weekday);
            }])
            ->where('doctor_id', $doctorId)
            ->where('active', true)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->where(function ($query) use ($date): void {
                $query->where('end_type', 'none')
                    ->orWhere(function ($subQuery) use ($date): void {
                        $subQuery->where('end_type', 'date')
                            ->whereDate('end_date', '>=', $date->toDateString());
                    })
                    ->orWhere('end_type', 'total_sessions');
            })
            ->get();

        $blocks = collect();

        foreach ($recurrings as $recurring) {
            if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
                if ($recurring->getGeneratedSessionsCount() >= (int) $recurring->total_sessions) {
                    continue;
                }
            }

            foreach ($recurring->rules as $rule) {
                $start = CarbonImmutable::parse(
                    $date->toDateString() . ' ' . $this->normalizeTime((string) $rule->start_time),
                    $this->timezone
                )->startOfMinute();

                $end = CarbonImmutable::parse(
                    $date->toDateString() . ' ' . $this->normalizeTime((string) $rule->end_time),
                    $this->timezone
                )->startOfMinute();

                if ($end->lte($start)) {
                    continue;
                }

                $blocks->push([
                    'starts_at' => $start,
                    'ends_at' => $end,
                ]);
            }
        }

        return $blocks;
    }

    /**
     * @param  Collection<int, array{starts_at:CarbonImmutable, ends_at:CarbonImmutable}>  $intervals
     */
    private function hasConflict(CarbonImmutable $start, CarbonImmutable $end, Collection $intervals): bool
    {
        return $intervals->contains(function (array $interval) use ($start, $end): bool {
            return $this->intervalsOverlap($start, $end, $interval['starts_at'], $interval['ends_at']);
        });
    }

    private function intervalsOverlap(
        CarbonImmutable $startA,
        CarbonImmutable $endA,
        CarbonImmutable $startB,
        CarbonImmutable $endB
    ): bool {
        return $startA->lt($endB) && $endA->gt($startB);
    }

    private function normalizeDate(CarbonInterface|string $date): CarbonImmutable
    {
        if ($date instanceof CarbonInterface) {
            return CarbonImmutable::instance($date)->setTimezone($this->timezone)->startOfDay();
        }

        return CarbonImmutable::parse($date, $this->timezone)->startOfDay();
    }

    private function normalizeTime(string $value): string
    {
        $time = trim($value);
        if (strlen($time) === 5) {
            return $time . ':00';
        }

        return $time;
    }
}
