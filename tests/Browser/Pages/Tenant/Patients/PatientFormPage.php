<?php

namespace Tests\Browser\Pages\Tenant\Patients;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class PatientFormPage extends Page
{
    public function __construct(
        private readonly ?string $patientId = null
    ) {
    }

    public static function edit(string $patientId): self
    {
        return new self($patientId);
    }

    public function url(): string
    {
        $slug = TenantTestContext::fromEnvironment()->slug;

        if ($this->patientId !== null) {
            return sprintf('/workspace/%s/patients/%s/edit', $slug, $this->patientId);
        }

        return sprintf('/workspace/%s/patients/create', $slug);
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@patient-form')
            ->assertVisible('@patient-full-name')
            ->assertVisible('@patient-cpf')
            ->assertVisible('@patient-submit-button');
    }

    public function elements(): array
    {
        return [
            '@patient-form' => '[dusk="patient-form"]',
            '@patient-full-name' => '[dusk="patient-full-name"]',
            '@patient-cpf' => '[dusk="patient-cpf"]',
            '@patient-submit-button' => '[dusk="patient-submit-button"]',
        ];
    }
}
