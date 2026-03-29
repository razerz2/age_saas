<?php

namespace Tests\Browser\Pages\Tenant\Attendances;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class AttendanceSessionPage extends Page
{
    public function __construct(
        private readonly string $date
    ) {
    }

    public function url(): string
    {
        return sprintf('/workspace/%s/atendimento/dia/%s', TenantTestContext::fromEnvironment()->slug, $this->date);
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@appointments-list')
            ->assertPresent('@appointment-details');
    }

    public function elements(): array
    {
        return [
            '@appointments-list' => '[dusk="medical-appointments-list"]',
            '@appointment-details' => '[dusk="medical-appointment-details"]',
            '@success-alert' => '[dusk="medical-session-success-alert"]',
            '@info-alert' => '[dusk="medical-session-info-alert"]',
            '@status-modal' => '[dusk="medical-status-modal"]',
            '@status-select' => '[dusk="medical-status-select"]',
            '@status-note' => '[dusk="medical-status-note"]',
            '@status-reschedule-at' => '[dusk="medical-status-reschedule-at"]',
            '@status-submit' => '[dusk="medical-status-submit"]',
            '@detail-status-badge' => '[dusk="medical-detail-status-badge"]',
        ];
    }

    public function queueItemSelector(string $appointmentId): string
    {
        return sprintf('[dusk="medical-queue-item-%s"]', $appointmentId);
    }

    public function openDetailsSelector(string $appointmentId): string
    {
        return sprintf('[dusk="medical-open-details-%s"]', $appointmentId);
    }

    public function openStatusModalSelector(string $appointmentId): string
    {
        return sprintf('[dusk="medical-open-status-modal-%s"]', $appointmentId);
    }

    public function completeAppointmentSelector(string $appointmentId): string
    {
        return sprintf('[dusk="medical-complete-appointment-%s"]', $appointmentId);
    }
}
