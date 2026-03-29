<?php

namespace Tests\Browser\Components\Tenant\Attendances;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

final class AttendanceStartForm extends Component
{
    public function selector(): string
    {
        return '[dusk="medical-start-form"]';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector())
            ->assertVisible('@date-input')
            ->assertVisible('@submit-button');
    }

    public function elements(): array
    {
        return [
            '@date-input' => '[dusk="medical-start-date"]',
            '@submit-button' => '[dusk="medical-start-submit-button"]',
        ];
    }

    public function selectDoctor(Browser $browser, string $doctorId): void
    {
        $browser->check(sprintf('[dusk="medical-start-doctor-%s"]', $doctorId));
    }

    public function setDate(Browser $browser, string $date): void
    {
        $dateValue = json_encode($date, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $browser->script(
            "const input = document.querySelector('[dusk=\"medical-start-date\"]');
            if (!input) { return; }
            input.value = {$dateValue};
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));"
        );
    }

    public function submit(Browser $browser): void
    {
        $browser->press('@submit-button');
    }
}
