@extends('layouts.tailadmin.public')

@section('title', 'Novo Agendamento — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public-appointment-create')


@section('content')

    <div id="public-appointment-create-config"
         data-tenant="{{ $tenant->subdomain }}"
         data-calendars-url-template="/customer/{{ $tenant->subdomain }}/agendamento/api/doctors/__ID__/calendars"
         data-appointment-types-url-template="/customer/{{ $tenant->subdomain }}/agendamento/api/doctors/__ID__/appointment-types"
         data-specialties-url-template="/customer/{{ $tenant->subdomain }}/agendamento/api/doctors/__ID__/specialties"
         data-available-slots-url-template="/customer/{{ $tenant->subdomain }}/agendamento/api/doctors/__ID__/available-slots?date=__DATE__"
         data-business-hours-url-template="/customer/{{ $tenant->subdomain }}/agendamento/api/doctors/__ID__/business-hours"
         data-old-doctor-id="{{ old('doctor_id') }}"
         data-old-date="{{ old('appointment_date') }}"
         data-old-appointment-type="{{ old('appointment_type') }}"
         data-old-specialty="{{ old('specialty_id') }}"></div>

    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8 py-10">

            {{-- Cabeçalho --}}
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                <i class="mdi mdi-calendar-plus text-lg"></i>
                            </span>
                            <span>Novo Agendamento</span>
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">
                            Preencha os dados abaixo para realizar seu agendamento.
                        </p>
                    </div>

                    @if(isset($patientName) && $patientName)
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs sm:text-sm font-medium text-slate-700 shadow-sm">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <i class="mdi mdi-account-circle text-base"></i>
                            </span>
                            <span class="uppercase tracking-wide text-[0.7rem] text-slate-500">Paciente</span>
                            <span class="text-slate-900">{{ $patientName }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mensagens --}}
            <div class="mb-6 space-y-3">
                @if (session('error'))
                    <div class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <i class="mdi mdi-alert-circle text-base"></i>
                        </span>
                        <div class="flex-1">
                            <p class="font-semibold">Ocorreu um problema</p>
                            <p class="mt-0.5">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <i class="mdi mdi-alert-circle text-base"></i>
                        </span>
                        <div class="flex-1">
                            <p class="font-semibold">Erro ao enviar o formulário</p>
                            <p class="mt-0.5">Por favor, verifique os campos abaixo.</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-5 sm:space-y-6">

                    <form class="space-y-5 sm:space-y-6" action="{{ route('public.appointment.store', ['slug' => $tenant->subdomain]) }}" method="POST">
                        @csrf

                        {{-- Card 1: Informações Básicas --}}
                        <div class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200 appointment-card">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                            <i class="mdi mdi-information-outline text-base"></i>
                                        </span>
                                        <span>Informações Básicas</span>
                                    </h2>
                                    <p class="mt-1 text-xs sm:text-sm text-slate-600">Selecione o médico, especialidade, tipo e modo de consulta.</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                {{-- Médico --}}
                                <div>
                                    <label for="doctor_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="mdi mdi-doctor text-slate-500 text-base"></i>
                                            <span>Médico</span>
                                            <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        name="doctor_id"
                                        id="doctor_id"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('doctor_id') border-red-300 focus:ring-red-500 @enderror"
                                        required
                                    >
                                        <option value="">Selecione um médico</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->user->name_full ?? $doctor->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Especialidade --}}
                                    <div>
                                        <label for="specialty_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="mdi mdi-stethoscope text-slate-500 text-base"></i>
                                                <span>Especialidade</span>
                                            </span>
                                        </label>
                                        <select
                                            name="specialty_id"
                                            id="specialty_id"
                                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-500 @error('specialty_id') border-red-300 focus:ring-red-500 @enderror"
                                            disabled
                                        >
                                            <option value="">Primeiro selecione um médico</option>
                                        </select>
                                        @error('specialty_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Tipo de Consulta --}}
                                    <div>
                                        <label for="appointment_type" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="mdi mdi-calendar-clock text-slate-500 text-base"></i>
                                                <span>Tipo de Consulta</span>
                                            </span>
                                        </label>
                                        <select
                                            name="appointment_type"
                                            id="appointment_type"
                                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-500 @error('appointment_type') border-red-300 focus:ring-red-500 @enderror"
                                            disabled
                                        >
                                            <option value="">Primeiro selecione um médico</option>
                                        </select>
                                        @error('appointment_type')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                @php
                                    $settings = \App\Models\Tenant\TenantSetting::getAll();
                                    $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                                @endphp
                                @if($defaultMode === 'user_choice')
                                    {{-- Modo de Consulta --}}
                                    <div>
                                        <label for="appointment_mode" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="mdi mdi-video-account text-slate-500 text-base"></i>
                                                <span>Modo de Consulta</span>
                                                <span class="text-red-500">*</span>
                                            </span>
                                        </label>
                                        <select
                                            name="appointment_mode"
                                            id="appointment_mode"
                                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('appointment_mode') border-red-300 focus:ring-red-500 @enderror"
                                            required
                                        >
                                            <option value="presencial" {{ old('appointment_mode', 'presencial') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                                            <option value="online" {{ old('appointment_mode') == 'online' ? 'selected' : '' }}>Online</option>
                                        </select>
                                        @error('appointment_mode')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card 2: Data e Horário --}}
                        <div class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200 appointment-card">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                            <i class="mdi mdi-clock-outline text-base"></i>
                                        </span>
                                        <span>Data e Horário</span>
                                    </h2>
                                    <p class="mt-1 text-xs sm:text-sm text-slate-600">Escolha a melhor data e horário com base na agenda do médico.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Data --}}
                                <div>
                                    <label for="appointment_date" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="mdi mdi-calendar-start text-slate-500 text-base"></i>
                                            <span>Data</span>
                                            <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                    <div class="flex items-stretch gap-2">
                                        <input
                                            type="date"
                                            id="appointment_date"
                                            name="appointment_date"
                                            value="{{ old('appointment_date') }}"
                                            min="{{ date('Y-m-d') }}"
                                            required
                                            class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('appointment_date') border-red-300 focus:ring-red-500 @enderror"
                                        >
                                        <x-tailadmin-button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            id="btn-show-business-hours"
                                            class="min-w-[44px] px-2 py-2 flex items-center justify-center"
                                            data-bs-toggle="modal"
                                            data-bs-target="#businessHoursModal"
                                            title="Ver dias trabalhados do médico"
                                            disabled
                                        >
                                            <i class="mdi mdi-calendar-clock text-base"></i>
                                        </x-tailadmin-button>
                                    </div>
                                    @error('appointment_date')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Horário Disponível --}}
                                <div>
                                    <label for="appointment_time" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="mdi mdi-clock-outline text-slate-500 text-base"></i>
                                            <span>Horário Disponível</span>
                                            <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        name="appointment_time"
                                        id="appointment_time"
                                        required
                                        disabled
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-500 @error('appointment_time') border-red-300 focus:ring-red-500 @enderror"
                                    >
                                        <option value="">Primeiro selecione a data</option>
                                    </select>
                                    @error('appointment_time')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-slate-500">Horários disponíveis baseados nas configurações do médico.</p>
                                </div>
                            </div>

                            <input type="hidden" name="starts_at" id="starts_at">
                            <input type="hidden" name="ends_at" id="ends_at">
                            <input type="hidden" name="calendar_id" id="calendar_id">
                        </div>

                        {{-- Card 3: Observações --}}
                        <div class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200 appointment-card">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                            <i class="mdi mdi-information text-base"></i>
                                        </span>
                                        <span>Observações</span>
                                    </h2>
                                    <p class="mt-1 text-xs sm:text-sm text-slate-600">Adicione informações adicionais importantes para o atendimento.</p>
                                </div>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1.5">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-note-text text-slate-500 text-base"></i>
                                        <span>Observações</span>
                                    </span>
                                </label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="4"
                                    placeholder="Digite observações sobre o agendamento (opcional)"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-300 focus:ring-red-500 @enderror"
                                >{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="mt-8 flex items-center justify-between">
                            <a
                                href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            >
                                <i class="mdi mdi-arrow-left text-base"></i>
                                <span>Voltar</span>
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <i class="mdi mdi-content-save text-base"></i>
                                <span>Confirmar Agendamento</span>
                            </button>
                        </div>

                    </form>

            </div>
        </div>
    </div>

    {{-- Modal de Dias Trabalhados --}}
    <div id="businessHoursModal" class="fixed inset-0 z-999999 hidden" tabindex="-1" aria-labelledby="businessHoursModalLabel" aria-hidden="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" data-bs-dismiss="modal" aria-hidden="true"></div>

            <div class="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2 dark:text-white" id="businessHoursModalLabel">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <i class="mdi mdi-calendar-clock text-base"></i>
                        </span>
                        <span>Dias Trabalhados do Médico</span>
                    </h2>

                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800" data-bs-dismiss="modal" aria-label="Fechar">
                        <i class="mdi mdi-close text-lg"></i>
                    </button>
                </div>

                <div class="px-5 py-4">
                    <div id="business-hours-loading" class="text-center py-6">
                        <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-solid border-indigo-500 border-t-transparent"></div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Carregando informações...</p>
                    </div>

                    <div id="business-hours-content" class="hidden">
                        <div class="mb-4 text-sm text-slate-700 dark:text-slate-200">
                            <span class="font-semibold">Médico:</span> <span id="business-hours-doctor-name">-</span>
                        </div>

                        <div id="business-hours-list"></div>

                        <div id="business-hours-empty" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <div class="flex items-start gap-2">
                                <i class="mdi mdi-information-outline text-lg text-indigo-600 dark:text-indigo-400"></i>
                                <span>Nenhum dia trabalhado configurado para este médico.</span>
                            </div>
                        </div>
                    </div>

                    <div id="business-hours-error" class="hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <div class="flex items-start gap-2">
                            <i class="mdi mdi-alert-circle text-lg"></i>
                            <span id="business-hours-error-message">Erro ao carregar informações.</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

