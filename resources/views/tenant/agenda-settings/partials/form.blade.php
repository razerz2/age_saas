@php
    $weekdays = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    $weekdayOrder = [1, 2, 3, 4, 5, 6, 0];

    $hoursRows = old('business_hours', $businessHoursRows ?? []);
    $hoursRows = collect($hoursRows)->map(function ($hour) {
        return [
            'weekday' => $hour['weekday'] ?? '',
            'start_time' => isset($hour['start_time']) ? substr((string) $hour['start_time'], 0, 5) : '',
            'end_time' => isset($hour['end_time']) ? substr((string) $hour['end_time'], 0, 5) : '',
            'break_start_time' => isset($hour['break_start_time']) ? substr((string) $hour['break_start_time'], 0, 5) : '',
            'break_end_time' => isset($hour['break_end_time']) ? substr((string) $hour['break_end_time'], 0, 5) : '',
        ];
    })->values()->all();

    $typesRows = old('appointment_types', $appointmentTypesRows ?? []);
    if (empty($typesRows)) {
        $typesRows = [[
            'id' => '',
            'name' => '',
            'duration_min' => 30,
            'is_active' => '1',
        ]];
    }
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-6" id="agenda-settings-form">
    @csrf
    @if (!empty($formMethod) && strtoupper($formMethod) !== 'POST')
        @method($formMethod)
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Dados principais</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="doctor_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Profissional <span class="text-red-500">*</span></label>
                @if(($isEdit ?? false) && isset($calendar))
                    <input
                        type="text"
                        value="{{ optional($calendar->doctor->user)->display_name ?? optional($calendar->doctor->user)->name }}"
                        disabled
                        class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                    <input type="hidden" name="doctor_id" value="{{ old('doctor_id', $calendar->doctor_id) }}">
                @else
                    <select
                        id="doctor_id"
                        name="doctor_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                        required
                    >
                        <option value="">Selecione...</option>
                        @foreach ($doctors as $doctor)
                            @php
                                $doctorName = $doctor->user->display_name ?? $doctor->user->name ?? 'Profissional';
                                $oldDoctor = old('doctor_id', request('doctor_id'));
                            @endphp
                            <option value="{{ $doctor->id }}" {{ (string) $oldDoctor === (string) $doctor->id ? 'selected' : '' }}>
                                {{ $doctorName }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome da agenda <span class="text-red-500">*</span></label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $calendar->name ?? '') }}"
                    placeholder="Ex: Agenda Principal"
                    required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                >
            </div>

            <div>
                <label for="external_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Identificador externo</label>
                <input
                    id="external_id"
                    type="text"
                    name="external_id"
                    value="{{ old('external_id', $calendar->external_id ?? '') }}"
                    placeholder="Opcional"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                >
            </div>

            <div class="md:col-span-1">
                <label for="is_active" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status da agenda <span class="text-red-500">*</span></label>
                <select
                    id="is_active"
                    name="is_active"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                    required
                >
                    @php
                        $agendaStatusValue = old('is_active', isset($calendar) ? ($calendar->is_active ? '1' : '0') : '1');
                    @endphp
                    <option value="1" {{ (string) $agendaStatusValue === '1' ? 'selected' : '' }}>Ativa</option>
                    <option value="0" {{ (string) $agendaStatusValue === '0' ? 'selected' : '' }}>Inativa</option>
                </select>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Horários de atendimento</h2>
            <button type="button" id="open-business-hour-modal" class="btn btn-outline inline-flex items-center">
                <x-icon name="plus" size="text-sm" class="mr-2" />
                Adicionar horário
            </button>
        </div>

        <div
            id="agenda-business-hours-section"
            data-weekdays='@json($weekdays)'
            data-weekday-order='@json($weekdayOrder)'
            data-initial-hours='@json($hoursRows)'
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Dia</th>
                            <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Atendimento</th>
                            <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Intervalo</th>
                            <th class="px-3 py-2 text-right text-xs uppercase text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="business-hours-table-body" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
            </div>

            <p id="business-hours-empty-state" class="mt-3 text-sm text-gray-500 dark:text-gray-400 hidden">
                Nenhum horário cadastrado. Dias ausentes serão considerados sem atendimento.
            </p>

            <div id="business-hours-hidden-inputs">
                @foreach ($hoursRows as $index => $hour)
                    <input type="hidden" name="business_hours[{{ $index }}][weekday]" value="{{ $hour['weekday'] ?? '' }}">
                    <input type="hidden" name="business_hours[{{ $index }}][start_time]" value="{{ $hour['start_time'] ?? '' }}">
                    <input type="hidden" name="business_hours[{{ $index }}][end_time]" value="{{ $hour['end_time'] ?? '' }}">
                    <input type="hidden" name="business_hours[{{ $index }}][break_start_time]" value="{{ $hour['break_start_time'] ?? '' }}">
                    <input type="hidden" name="business_hours[{{ $index }}][break_end_time]" value="{{ $hour['break_end_time'] ?? '' }}">
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tipos vinculados</h2>
            <button type="button" id="add-appointment-type-row" class="btn btn-outline inline-flex items-center">
                <x-icon name="plus" size="text-sm" class="mr-2" />
                Adicionar tipo
            </button>
        </div>

        <div class="space-y-3" id="appointment-types-rows">
            @foreach ($typesRows as $index => $type)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/30 appointment-type-row">
                    <input type="hidden" name="appointment_types[{{ $index }}][id]" value="{{ $type['id'] ?? '' }}">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Nome</label>
                            <input type="text" name="appointment_types[{{ $index }}][name]" value="{{ $type['name'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Duração (min)</label>
                            <input type="number" min="1" name="appointment_types[{{ $index }}][duration_min]" value="{{ $type['duration_min'] ?? 30 }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Status</label>
                            @php
                                $isTypeActive = (string) ($type['is_active'] ?? '1') === '1';
                            @endphp
                            <select name="appointment_types[{{ $index }}][is_active]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                <option value="1" {{ $isTypeActive ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ !$isTypeActive ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        <div class="flex items-end justify-end">
                            <button type="button" class="btn btn-outline text-red-600 hover:text-red-700 remove-appointment-type-row inline-flex items-center">
                                <x-icon name="trash-can-outline" size="text-sm" class="mr-2" />
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="flex items-center justify-end gap-2">
        <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="btn btn-outline inline-flex items-center">
            <x-icon name="close" size="text-sm" class="mr-2" />
            Cancelar
        </a>
        <button type="submit" class="btn btn-primary inline-flex items-center">
            <x-icon name="content-save-outline" size="text-sm" class="mr-2" />
            {{ $submitLabel }}
        </button>
    </div>
</form>

<div id="business-hour-modal" class="fixed inset-0 hidden p-4" style="z-index:2147483646;" role="dialog" aria-modal="true" aria-labelledby="business-hour-modal-title">
    <div class="absolute inset-0 bg-black/50" data-business-hour-modal-close aria-hidden="true"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4" style="z-index:2147483647;">
        <div class="relative w-full max-w-3xl rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-700 dark:bg-gray-900">
            <button type="button" class="absolute right-3 top-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" data-business-hour-modal-close aria-label="Fechar">
                <x-icon name="close-outline" class="h-5 w-5" />
            </button>

            <div class="mb-4 pr-8">
                <h3 id="business-hour-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">Adicionar horário</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Defina o dia e os horários de atendimento.</p>
            </div>

            <div id="business-hour-modal-error" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-200"></div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="business-hour-modal-weekday" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Dia da semana <span class="text-red-500">*</span></label>
                    <select id="business-hour-modal-weekday" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></select>
                </div>

                <div>
                    <label for="business-hour-modal-start-time" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Hora inicial <span class="text-red-500">*</span></label>
                    <input id="business-hour-modal-start-time" type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label for="business-hour-modal-end-time" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Hora final <span class="text-red-500">*</span></label>
                    <input id="business-hour-modal-end-time" type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input id="business-hour-modal-has-break" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-900">
                        Possui intervalo
                    </label>
                </div>

                <div>
                    <label for="business-hour-modal-break-start" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Início do intervalo</label>
                    <input id="business-hour-modal-break-start" type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label for="business-hour-modal-break-end" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fim do intervalo</label>
                    <input id="business-hour-modal-break-end" type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="btn btn-outline inline-flex items-center" data-business-hour-modal-close>
                    <x-icon name="close" size="text-sm" class="mr-2" />
                    Cancelar
                </button>
                <button type="button" id="save-business-hour-modal" class="btn btn-primary inline-flex items-center">
                    <x-icon name="content-save-outline" size="text-sm" class="mr-2" />
                    Salvar horário
                </button>
            </div>
        </div>
    </div>
</div>

<template id="appointment-type-row-template">
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/30 appointment-type-row">
        <input type="hidden" name="appointment_types[__INDEX__][id]" value="">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Nome</label>
                <input type="text" name="appointment_types[__INDEX__][name]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Duração (min)</label>
                <input type="number" min="1" name="appointment_types[__INDEX__][duration_min]" value="30" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Status</label>
                <select name="appointment_types[__INDEX__][is_active]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                </select>
            </div>
            <div class="flex items-end justify-end">
                <button type="button" class="btn btn-outline text-red-600 hover:text-red-700 remove-appointment-type-row inline-flex items-center">
                    <x-icon name="trash-can-outline" size="text-sm" class="mr-2" />
                    Remover
                </button>
            </div>
        </div>
    </div>
</template>
