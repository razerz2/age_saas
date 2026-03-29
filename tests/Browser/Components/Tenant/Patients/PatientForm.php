<?php

namespace Tests\Browser\Components\Tenant\Patients;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component;

final class PatientForm extends Component
{
    public function selector(): string
    {
        return '[dusk="patient-form"]';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector())
            ->assertVisible('@full-name')
            ->assertVisible('@cpf')
            ->assertVisible('@submit-button');
    }

    public function elements(): array
    {
        return [
            '@full-name' => '[dusk="patient-full-name"]',
            '@cpf' => '[dusk="patient-cpf"]',
            '@street' => '[dusk="patient-street"]',
            '@number' => '[dusk="patient-number"]',
            '@neighborhood' => '[dusk="patient-neighborhood"]',
            '@postal-code' => '[dusk="patient-postal-code"]',
            '@state' => '[dusk="patient-state"]',
            '@city' => '[dusk="patient-city"]',
            '@submit-button' => '[dusk="patient-submit-button"]',
        ];
    }

    /**
     * @param array<string, string> $patient
     */
    public function fillAndSubmit(Browser $browser, array $patient): void
    {
        $this->fillFields($browser, $patient);
        $this->submit($browser);
    }

    /**
     * @param array<string, string> $patient
     */
    public function updateAndSubmit(Browser $browser, array $patient): void
    {
        $this->fillFields($browser, $patient);
        $this->submit($browser);
    }

    /**
     * @param array<string, string> $patient
     */
    private function fillFields(Browser $browser, array $patient): void
    {
        $this->setValue($browser, '@full-name', $patient['full_name']);

        if (array_key_exists('cpf', $patient)) {
            $this->setValue($browser, '@cpf', $patient['cpf']);
        }

        $this->setValue($browser, '@street', $patient['street']);
        $this->setValue($browser, '@number', $patient['number']);
        $this->setValue($browser, '@neighborhood', $patient['neighborhood']);
        $this->setValue($browser, '@postal-code', $patient['postal_code']);

        $this->ensureStateAndCitySelected($browser);
    }

    private function submit(Browser $browser): void
    {
        $browser->press('@submit-button');
    }

    private function setValue(Browser $browser, string $selector, string $value): void
    {
        $browser->clear($selector)->type($selector, $value);
    }

    private function ensureStateAndCitySelected(Browser $browser): void
    {
        $browser->waitUsing(20, 200, function () use ($browser) {
            return $this->hasSelectableState($browser);
        }, 'Nao foi possivel carregar estados no formulario de paciente.');

        // Mantem estado atual (quando CEP preencher) ou seleciona o primeiro estado valido.
        $browser->script(
            "const state = document.querySelector('[dusk=\"patient-state\"]');
            if (!state) { return null; }
            if (!state.value) {
                const option = Array.from(state.options).find((item) => item.value !== '');
                if (option) { state.value = option.value; }
            }
            state.dispatchEvent(new Event('change', { bubbles: true }));
            return state.value;"
        );

        $browser->waitUsing(20, 200, function () use ($browser) {
            return $this->hasSelectableCity($browser);
        }, 'Nao foi possivel carregar cidades no formulario de paciente.');

        $browser->script(
            "const city = document.querySelector('[dusk=\"patient-city\"]');
            if (!city) { return null; }
            if (!city.value) {
                const option = Array.from(city.options).find((item) => item.value !== '');
                if (option) { city.value = option.value; }
            }
            city.dispatchEvent(new Event('change', { bubbles: true }));
            return city.value;"
        );
    }

    private function hasSelectableState(Browser $browser): bool
    {
        $result = $browser->script(
            "const state = document.querySelector('[dusk=\"patient-state\"]');
            if (!state) { return false; }
            return Array.from(state.options).some((item) => item.value !== '');"
        );

        return (bool) ($result[0] ?? false);
    }

    private function hasSelectableCity(Browser $browser): bool
    {
        $result = $browser->script(
            "const city = document.querySelector('[dusk=\"patient-city\"]');
            if (!city) { return false; }
            return Array.from(city.options).some((item) => item.value !== '');"
        );

        return (bool) ($result[0] ?? false);
    }
}
