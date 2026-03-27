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

    $hoursRows = old('business_hours', $businessHoursRows ?? []);
    if (empty($hoursRows)) {
        $hoursRows = [[
            'weekday' => 1,
            'start_time' => '',
            'end_time' => '',
            'break_start_time' => '',
            'break_end_time' => '',
        ]];
    }

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
            <button type="button" id="add-business-hour-row" class="btn btn-outline">Adicionar horário</button>
        </div>

        <div class="space-y-3" id="business-hours-rows">
            @foreach ($hoursRows as $index => $hour)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/30 business-hour-row">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Dia</label>
                            <select name="business_hours[{{ $index }}][weekday]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                @foreach ($weekdays as $weekdayValue => $weekdayLabel)
                                    <option value="{{ $weekdayValue }}" {{ (string) ($hour['weekday'] ?? '') === (string) $weekdayValue ? 'selected' : '' }}>{{ $weekdayLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Início</label>
                            <input type="time" name="business_hours[{{ $index }}][start_time]" value="{{ $hour['start_time'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Fim</label>
                            <input type="time" name="business_hours[{{ $index }}][end_time]" value="{{ $hour['end_time'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Início intervalo</label>
                            <input type="time" name="business_hours[{{ $index }}][break_start_time]" value="{{ $hour['break_start_time'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Fim intervalo</label>
                            <input type="time" name="business_hours[{{ $index }}][break_end_time]" value="{{ $hour['break_end_time'] ?? '' }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div class="flex items-end justify-end">
                            <button type="button" class="btn btn-outline text-red-600 hover:text-red-700 remove-business-hour-row">Remover</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tipos vinculados</h2>
            <button type="button" id="add-appointment-type-row" class="btn btn-outline">Adicionar tipo</button>
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
                            <button type="button" class="btn btn-outline text-red-600 hover:text-red-700 remove-appointment-type-row">Remover</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="flex items-center justify-end gap-2">
        <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

<template id="business-hour-row-template">
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/30 business-hour-row">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Dia</label>
                <select name="business_hours[__INDEX__][weekday]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    @foreach ($weekdays as $weekdayValue => $weekdayLabel)
                        <option value="{{ $weekdayValue }}">{{ $weekdayLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Início</label>
                <input type="time" name="business_hours[__INDEX__][start_time]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Fim</label>
                <input type="time" name="business_hours[__INDEX__][end_time]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Início intervalo</label>
                <input type="time" name="business_hours[__INDEX__][break_start_time]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Fim intervalo</label>
                <input type="time" name="business_hours[__INDEX__][break_end_time]" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div class="flex items-end justify-end">
                <button type="button" class="btn btn-outline text-red-600 hover:text-red-700 remove-business-hour-row">Remover</button>
            </div>
        </div>
    </div>
</template>

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
                <button type="button" class="btn btn-outline text-red-600 hover:text-red-700 remove-appointment-type-row">Remover</button>
            </div>
        </div>
    </div>
</template>
