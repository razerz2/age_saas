<?php

namespace App\DTO\Tenant;

use App\Support\Tenant\OnlineMeeting;

class OnlineMeetingResult
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $status,
        public readonly ?string $provider = null,
        public readonly ?string $meetingLink = null,
        public readonly ?string $externalEventId = null,
        public readonly ?string $externalMeetingId = null,
        public readonly ?string $errorMessage = null,
        public readonly array $meta = []
    ) {
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function success(
        ?string $provider = null,
        ?string $meetingLink = null,
        ?string $externalEventId = null,
        ?string $externalMeetingId = null,
        array $meta = [],
        string $status = OnlineMeeting::STATUS_GENERATED
    ): self {
        return new self(
            success: true,
            status: $status,
            provider: $provider,
            meetingLink: $meetingLink,
            externalEventId: $externalEventId,
            externalMeetingId: $externalMeetingId,
            meta: $meta
        );
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function failed(
        ?string $provider = null,
        ?string $errorMessage = null,
        array $meta = [],
        string $status = OnlineMeeting::STATUS_FAILED
    ): self {
        return new self(
            success: false,
            status: $status,
            provider: $provider,
            errorMessage: $errorMessage,
            meta: $meta
        );
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function manualRequired(
        ?string $provider = null,
        ?string $errorMessage = null,
        array $meta = [],
        ?string $meetingLink = null,
        ?string $externalEventId = null,
        ?string $externalMeetingId = null
    ): self {
        return new self(
            success: false,
            status: OnlineMeeting::STATUS_MANUAL_REQUIRED,
            provider: $provider,
            meetingLink: $meetingLink,
            externalEventId: $externalEventId,
            externalMeetingId: $externalMeetingId,
            errorMessage: $errorMessage,
            meta: $meta
        );
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function skipped(
        ?string $provider = null,
        ?string $errorMessage = null,
        array $meta = [],
        ?string $meetingLink = null,
        ?string $externalEventId = null,
        ?string $externalMeetingId = null
    ): self {
        return new self(
            success: true,
            status: OnlineMeeting::STATUS_SKIPPED,
            provider: $provider,
            meetingLink: $meetingLink,
            externalEventId: $externalEventId,
            externalMeetingId: $externalMeetingId,
            errorMessage: $errorMessage,
            meta: $meta
        );
    }
}
