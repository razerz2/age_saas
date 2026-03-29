<?php

namespace Tests\Browser\Pages\Tenant\Appointments;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class AppointmentIndexPage extends Page
{
    public function url(): string
    {
        return sprintf('/workspace/%s/appointments', TenantTestContext::fromEnvironment()->slug);
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@appointments-grid-wrapper')
            ->assertVisible('@new-appointment-button');
    }

    public function elements(): array
    {
        return [
            '@appointments-grid-wrapper' => '[dusk="appointments-grid-wrapper"]',
            '@new-appointment-button' => '[dusk="appointments-new-button"]',
            '@success-alert' => '[dusk="appointments-success-alert"]',
            '@error-alert' => '[dusk="appointments-error-alert"]',
            '@grid-search-input' => '.gridjs-search input',
        ];
    }

    public function editActionSelector(string $appointmentId): string
    {
        return sprintf('[dusk="appointments-edit-action-%s"]', $appointmentId);
    }

    public function deleteActionSelector(string $appointmentId): string
    {
        return sprintf('[dusk="appointments-delete-action-%s"]', $appointmentId);
    }
}
