@extends('layouts.tailadmin.public')

@section('title', 'Novo Agendamento — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

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
        <div class="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8 pt-10 pb-10 sm:pt-12 sm:pb-12 lg:pt-14 lg:pb-14">

            {{-- Cabeçalho --}}
            <div class="mb-6 sm:mb-8 text-center">
                <div class="mx-auto flex max-w-2xl flex-col items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-1">
                        <i class="mdi mdi-calendar-plus text-xl"></i>
                    </span>
                    <div class="w-full">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 text-center">
                            Novo Agendamento
                        </h1>
                        <p class="mt-1 text-sm text-slate-600 text-center">
                            Preencha os dados abaixo para realizar seu agendamento.
                        </p>
                    </div>
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

            <form action="{{ route('public.appointment.store', ['slug' => $tenant->subdomain]) }}" method="POST">
                @csrf

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    {{-- Seção 1: Informações Básicas --}}
                    <div class="p-5 sm:p-6">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                    <i class="mdi mdi-information-outline text-base"></i>
                                </span>
                                <span>Informações Básicas</span>
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-600">Selecione o médico, especialidade e modo de consulta.</p>
                        </div>

                        <div class="space-y-4">
                            @if(isset($patientName) && $patientName)
                                <div class="inline-flex w-full items-center justify-center sm:justify-start gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs sm:text-sm font-medium text-slate-700">
                                    <span class="uppercase tracking-wide text-[0.7rem] text-slate-500">Paciente</span>
                                    <span class="text-slate-900">{{ $patientName }}</span>
                                </div>
                            @endif

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

                            @php
                                $settings = \App\Models\Tenant\TenantSetting::getAll();
                                $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                            @endphp

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

                            {{-- Tipo de Consulta (mantém no DOM para compatibilidade com JS/back-end; oculto na UI) --}}
                            <div data-appointment-type-wrapper class="hidden">
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
                    </div>

                    {{-- Seção 2: Data e Horário --}}
                    <div class="border-t border-slate-200 p-5 sm:p-6">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                    <i class="mdi mdi-clock-outline text-base"></i>
                                </span>
                                <span>Data e Horário</span>
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-600">Escolha a melhor data e horário com base na agenda do médico.</p>
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
                                        value="{{ old('appointment_date') ?: date('Y-m-d') }}"
                                        min="{{ date('Y-m-d') }}"
                                        required
                                        class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('appointment_date') border-red-300 focus:ring-red-500 @enderror"
                                    >
                                    <button
                                        type="button"
                                        id="btn-show-business-hours"
                                        class="inline-flex min-w-[44px] items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-2 text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed"
                                        title="Ver dias trabalhados do médico"
                                        disabled
                                    >
                                        <i class="mdi mdi-calendar-clock text-base text-slate-900"></i>
                                    </button>
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

                    {{-- Seção 3: Observações --}}
                    <div class="border-t border-slate-200 p-5 sm:p-6">
                        <div class="mb-4">
                            <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                    <i class="mdi mdi-information text-base"></i>
                                </span>
                                <span>Observações</span>
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-600">Adicione informações adicionais importantes para o atendimento.</p>
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
                    <div class="border-t border-slate-200 p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <a
                                href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}"
                                class="btn btn-outline"
                            >
                                <i class="mdi mdi-arrow-left text-base text-slate-900"></i>
                                <span>Voltar</span>
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <i class="mdi mdi-content-save text-base text-white"></i>
                                <span>Confirmar Agendamento</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal de Dias Trabalhados --}}
    <div id="businessHoursModal" class="fixed inset-0 z-999999 hidden" tabindex="-1" aria-labelledby="businessHoursModalLabel" aria-hidden="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" data-modal-dismiss="businessHoursModal" aria-hidden="true"></div>

            <div class="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 max-h-[calc(100vh-2rem)] flex flex-col">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <h2 class="text-base font-semibold leading-6 text-slate-900 flex items-center gap-2" id="businessHoursModalLabel">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <i class="mdi mdi-calendar-clock text-base"></i>
                        </span>
                        <span>Dias Trabalhados do Médico</span>
                    </h2>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        data-modal-dismiss="businessHoursModal"
                        aria-label="Fechar"
                    >
                        <i class="mdi mdi-close text-lg text-slate-900"></i>
                    </button>
                </div>

                <div class="px-5 py-4 overflow-y-auto">
                    <div id="business-hours-loading" class="text-center py-6">
                        <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-solid border-indigo-500 border-t-transparent"></div>
                        <p class="mt-3 text-sm text-slate-600">Carregando informações...</p>
                    </div>

                    <div id="business-hours-content" class="hidden">
                        <div class="mb-4 text-sm text-slate-700 text-center">
                            <span class="font-semibold">Médico:</span> <span id="business-hours-doctor-name">-</span>
                        </div>

                        <div id="business-hours-list"></div>

                        <div id="business-hours-empty" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="flex items-start gap-2">
                                <i class="mdi mdi-information-outline text-lg text-indigo-600"></i>
                                <span>Nenhum dia trabalhado configurado para este médico.</span>
                            </div>
                        </div>
                    </div>

                    <div id="business-hours-error" class="hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <div class="flex items-start gap-2">
                            <i class="mdi mdi-alert-circle text-lg"></i>
                            <span id="business-hours-error-message">Erro ao carregar informações.</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-5 py-4">
                    <button
                        type="button"
                        class="btn btn-outline"
                        data-modal-dismiss="businessHoursModal"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
