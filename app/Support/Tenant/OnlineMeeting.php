<?php

namespace App\Support\Tenant;

class OnlineMeeting
{
    public const PROVIDER_GOOGLE_MEET = 'google_meet';
    public const PROVIDER_MANUAL = 'manual';

    public const STATUS_PENDING = 'pending';
    public const STATUS_GENERATED = 'generated';
    public const STATUS_FAILED = 'failed';
    public const STATUS_MANUAL_REQUIRED = 'manual_required';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_SKIPPED = 'skipped';

    public const GENERATION_ON_CREATED = 'on_created';
    public const GENERATION_ON_CONFIRMED = 'on_confirmed';

    public const FAILURE_KEEP_APPOINTMENT_PENDING_MEETING = 'keep_appointment_pending_meeting';
    public const FAILURE_BLOCK_ONLINE_APPOINTMENT = 'block_online_appointment';

    /**
     * @return array<int, string>
     */
    public static function providers(): array
    {
        return [
            self::PROVIDER_GOOGLE_MEET,
            self::PROVIDER_MANUAL,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_GENERATED,
            self::STATUS_FAILED,
            self::STATUS_MANUAL_REQUIRED,
            self::STATUS_CANCELLED,
            self::STATUS_SKIPPED,
        ];
    }

    public static function isValidProvider(?string $provider): bool
    {
        if ($provider === null || trim($provider) === '') {
            return false;
        }

        $normalized = strtolower(trim((string) $provider));

        return in_array($normalized, self::providers(), true);
    }

    public static function isValidStatus(?string $status): bool
    {
        if ($status === null || trim($status) === '') {
            return false;
        }

        return in_array($status, self::statuses(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function generationTimings(): array
    {
        return [
            self::GENERATION_ON_CREATED,
            self::GENERATION_ON_CONFIRMED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function failurePolicies(): array
    {
        return [
            self::FAILURE_KEEP_APPOINTMENT_PENDING_MEETING,
            self::FAILURE_BLOCK_ONLINE_APPOINTMENT,
        ];
    }

    public static function isValidGenerationTiming(?string $value): bool
    {
        return in_array(self::normalizeGenerationTiming($value), self::generationTimings(), true);
    }

    public static function normalizeProvider(?string $provider): string
    {
        $normalized = strtolower(trim((string) $provider));

        return in_array($normalized, self::providers(), true)
            ? $normalized
            : self::PROVIDER_GOOGLE_MEET;
    }

    public static function normalizeGenerationTiming(?string $timing): string
    {
        $normalized = strtolower(trim((string) $timing));

        return in_array($normalized, self::generationTimings(), true)
            ? $normalized
            : self::GENERATION_ON_CONFIRMED;
    }
}
