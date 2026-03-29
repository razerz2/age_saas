<?php

namespace Tests\Browser\Components\Tenant\Appointments;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;
use RuntimeException;

final class AppointmentForm extends Component
{
    public function selector(): string
    {
        return '[dusk="appointment-form"]';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector())
            ->assertVisible('@appointment-date')
            ->assertVisible('@appointment-time')
            ->assertVisible('@appointment-submit-button');
    }

    public function elements(): array
    {
        return [
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

    public function selectPatient(Browser $browser, string $query): void
    {
        $this->selectEntity($browser, '@patient-search-button', $query);
    }

    public function selectDoctor(Browser $browser, string $query): void
    {
        $this->selectEntity($browser, '@doctor-search-button', $query);
    }

    public function setPatient(Browser $browser, string $patientId, string $patientName): void
    {
        $patientIdValue = json_encode($patientId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $patientNameValue = json_encode($patientName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $browser->script(
            "const hiddenInput = document.querySelector('[dusk=\"appointment-patient-id\"]');
            const displayInput = document.querySelector('[dusk=\"appointment-patient-name\"]');
            if (!hiddenInput || !displayInput) { return; }
            hiddenInput.value = {$patientIdValue};
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            displayInput.value = {$patientNameValue};"
        );

        $browser->pause(250);
    }

    public function setDoctor(Browser $browser, string $doctorId, string $doctorName): void
    {
        $doctorIdValue = json_encode($doctorId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $doctorNameValue = json_encode($doctorName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $browser->script(
            "const hiddenInput = document.querySelector('[dusk=\"appointment-doctor-id\"]');
            const displayInput = document.querySelector('[dusk=\"appointment-doctor-name\"]');
            if (!hiddenInput || !displayInput) { return; }
            hiddenInput.value = {$doctorIdValue};
            hiddenInput.dataset.selectedName = {$doctorNameValue};
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            hiddenInput.dispatchEvent(new CustomEvent('doctor:selected', { bubbles: true }));
            displayInput.value = {$doctorNameValue};"
        );

        $browser->pause(450);
    }

    public function setDate(Browser $browser, string $date): void
    {
        $dateValue = json_encode($date, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $browser->script(
            "const input = document.querySelector('[dusk=\"appointment-date\"]');
            if (!input) { return; }
            if (input.disabled) {
                input.disabled = false;
            }
            input.value = {$dateValue};
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));"
        );
        $browser->pause(500);
    }

    public function selectFirstFreeTimeSlot(Browser $browser): void
    {
        $browser->waitUsing(20, 200, function () use ($browser) {
            return $this->hasFreeSlotOption($browser);
        }, 'Nao foi possivel carregar horario livre para o agendamento.');

        $selected = $browser->script(
            "const select = document.querySelector('[dusk=\"appointment-time\"]');
            if (!select) { return ''; }
            const option = Array.from(select.options).find((item) => {
                if (!item.value || item.disabled) { return false; }
                const status = String(item.dataset.status || 'FREE').toUpperCase();
                return status === 'FREE';
            });
            return option ? option.value : '';"
        );

        $selectedValue = (string) ($selected[0] ?? '');
        if ($selectedValue === '') {
            throw new RuntimeException('Nenhum horario livre foi encontrado no formulario de agendamento.');
        }

        $value = json_encode($selectedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $browser->script(
            "const select = document.querySelector('[dusk=\"appointment-time\"]');
            if (!select) { return; }
            select.value = {$value};
            select.dispatchEvent(new Event('change', { bubbles: true }));"
        );

        $browser->pause(250);
    }

    public function setNotes(Browser $browser, string $notes): void
    {
        $browser->clear('@appointment-notes')
            ->type('@appointment-notes', $notes);
    }

    public function submit(Browser $browser): void
    {
        $browser->press('@appointment-submit-button');
    }

    private function selectEntity(Browser $browser, string $triggerSelector, string $query): void
    {
        $browser->click($triggerSelector)
            ->waitFor('@entity-search-modal', 10)
            ->waitFor('@entity-search-input', 10)
            ->clear('@entity-search-input')
            ->type('@entity-search-input', $query)
            ->pause(500);

        $browser->waitUsing(20, 200, function () use ($browser) {
            return $this->hasEntitySearchResult($browser);
        }, sprintf('Nao foi possivel encontrar resultado na busca por "%s".', $query));

        $browser->click('@entity-search-result-button')
            ->waitUntilEnabled('@entity-search-confirm-button', 10)
            ->press('@entity-search-confirm-button')
            ->pause(300);
    }

    private function hasEntitySearchResult(Browser $browser): bool
    {
        $result = $browser->script(
            "return document.querySelectorAll('.entity-search-modal__result').length > 0;"
        );

        return (bool) ($result[0] ?? false);
    }

    private function hasFreeSlotOption(Browser $browser): bool
    {
        $result = $browser->script(
            "const select = document.querySelector('[dusk=\"appointment-time\"]');
            if (!select) { return false; }
            return Array.from(select.options).some((item) => {
                if (!item.value || item.disabled) { return false; }
                const status = String(item.dataset.status || 'FREE').toUpperCase();
                return status === 'FREE';
            });"
        );

        return (bool) ($result[0] ?? false);
    }
}
