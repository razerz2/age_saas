<?php

namespace Tests\Browser\Pages\Tenant\Attendances;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;
use Tests\Browser\Support\TenantTestContext;

final class AttendanceStartPage extends Page
{
    public function url(): string
    {
        return sprintf('/workspace/%s/atendimento', TenantTestContext::fromEnvironment()->slug);
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
            ->assertPresent('@start-form')
            ->assertVisible('@date-input')
            ->assertVisible('@submit-button');
    }

    public function elements(): array
    {
        return [
            '@start-form' => '[dusk="medical-start-form"]',
            '@date-input' => '[dusk="medical-start-date"]',
            '@submit-button' => '[dusk="medical-start-submit-button"]',
        ];
    }

    public function doctorCheckboxSelector(string $doctorId): string
    {
        return sprintf('[dusk="medical-start-doctor-%s"]', $doctorId);
    }
}
