<?php

namespace Tests\Browser\Pages\Tenant\Appointments;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class AppointmentFormPage extends Page
{
    public function __construct(
        private readonly ?string $appointmentId = null
    ) {
    }

    public static function edit(string $appointmentId): self
    {
        return new self($appointmentId);
    }

    public function url(): string
    {
        $slug = TenantTestContext::fromEnvironment()->slug;

        if ($this->appointmentId !== null) {
            return sprintf('/workspace/%s/appointments/%s/edit', $slug, $this->appointmentId);
        }

        return sprintf('/workspace/%s/appointments/create', $slug);
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@appointment-form')
            ->assertVisible('@appointment-date')
            ->assertVisible('@appointment-time')
            ->assertVisible('@appointment-submit-button');
    }

    public function elements(): array
    {
        return [
            '@appointment-form' => '[dusk="appointment-form"]',
            '@patient-search-button' => '[dusk="appointment-search-patient-button"]',
            '@doctor-search-button' => '[dusk="appointment-search-doctor-button"]',
            '@appointment-date' => '[dusk="appointment-date"]',
            '@appointment-time' => '[dusk="appointment-time"]',
            '@appointment-notes' => '[dusk="appointment-notes"]',
            '@appointment-submit-button' => '[dusk="appointment-submit-button"]',
            '@entity-search-modal' => '[dusk="entity-search-modal"]',
            '@entity-search-input' => '[dusk="entity-search-input"]',
            '@entity-search-result-button' => '.entity-search-modal__result',
            '@entity-search-confirm-button' => '[dusk="entity-search-confirm-button"]',
        ];
    }
}
