<?php

namespace Tests\Browser\Pages\Tenant\Patients;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class PatientIndexPage extends Page
{
    public function url(): string
    {
        return sprintf('/workspace/%s/patients', TenantTestContext::fromEnvironment()->slug);
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@patients-grid-wrapper')
            ->assertVisible('@new-patient-button');
    }

    public function elements(): array
    {
        return [
            '@patients-grid-wrapper' => '[dusk="patients-grid-wrapper"]',
            '@new-patient-button' => '[dusk="patients-new-button"]',
            '@success-alert' => '[dusk="patients-success-alert"]',
            '@grid-search-input' => '.gridjs-search input',
            '@confirm-dialog' => '[dusk="global-confirm-dialog"]',
            '@confirm-dialog-title' => '[dusk="global-confirm-dialog-title"]',
            '@confirm-dialog-confirm-button' => '[dusk="global-confirm-dialog-confirm-button"]',
        ];
    }

    public function editActionSelector(string $patientId): string
    {
        return sprintf('[dusk="patients-edit-action-%s"]', $patientId);
    }

    public function deleteActionSelector(string $patientId): string
    {
        return sprintf('[dusk="patients-delete-action-%s"]', $patientId);
    }
}
