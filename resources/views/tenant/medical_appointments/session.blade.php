@extends('layouts.tailadmin.app')

@section('title', 'Atendimento - ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))
@section('page', 'medical_appointments')

@section('content')
    @php
        $initialAppointmentId = session('selected_appointment') ?? ($appointments && $appointments->count() > 0 ? $appointments->first()->id : null);
        $displayDate = \Carbon\Carbon::parse($date)->format('d/m/Y');
        $reorderUrl = workspace_route('tenant.medical-appointments.reorder', ['date' => $date]);
        if (request()->getQueryString()) {
            $reorderUrl .= '?' . request()->getQueryString();
        }
    @endphp

    <div id="medical-appointments-config"
         data-details-url-template="{{ workspace_route('tenant.medical-appointments.details', ['appointmentId' => '__ID__']) }}"
         data-update-status-url-template="{{ workspace_route('tenant.medical-appointments.update-status', ['appointmentId' => '__ID__']) }}"
         data-complete-url-template="{{ workspace_route('tenant.medical-appointments.complete', ['appointmentId' => '__ID__']) }}"
         data-form-response-url-template="{{ workspace_route('tenant.medical-appointments.form-response', ['appointmentId' => '__ID__']) }}"
         data-reorder-url="{{ $reorderUrl }}"
         data-current-date="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
         data-csrf="{{ csrf_token() }}"
         data-initial-id="{{ $initialAppointmentId }}"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Atendimento do Dia</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $displayDate }}</p>
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                   class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                    <x-icon name="home-outline" size="text-base" class="mr-2" />
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                    <a href="{{ workspace_route('tenant.medical-appointments.index') }}"
                                       class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                        Atendimento
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">{{ $displayDate }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                <div class="flex items-start">
                    <x-icon name="check-circle-outline" size="text-lg" class="text-green-600 dark:text-green-400" />
                    <p class="ml-3 text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                <div class="flex items-start">
                    <x-icon name="information-outline" size="text-lg" class="text-blue-600 dark:text-blue-400" />
                    <p class="ml-3 text-sm text-blue-800 dark:text-blue-200">{{ session('info') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <section class="xl:col-span-4 min-w-0 min-h-0">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full min-h-0 flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Agendamentos do Dia</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Clique para abrir os detalhes.</p>
                    </div>

                    <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/30">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2">
                                <label for="appointments-sort-by" class="text-xs font-medium text-gray-600 dark:text-gray-300">Ordenar por</label>
                                <div class="relative">
                                    <select id="appointments-sort-by"
                                        class="h-9 appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-10 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        <option value="manual">Ordem manual</option>
                                        <option value="horario">Horário</option>
                                        <option value="status">Status</option>
                                        <option value="paciente">Paciente</option>
                                        <option value="medico">Médico</option>
                                        <option value="tipo">Tipo</option>
                                    </select>
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="mdi mdi-chevron-down text-base"></i>
                                    </span>
                                </div>
                            </div>

                            <button type="button"
                                    id="appointments-sort-dir"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                    data-direction="asc">
                                <i class="mdi mdi-sort-ascending mr-1 text-sm"></i>
                                Crescente
                            </button>
                        </div>
                        <p id="manual-order-help" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Modo manual ativo: arraste pelos Ã­cones para reordenar a fila.
                        </p>
                    </div>

                    <div data-queue-scroll="1" class="queue-scroll flex-1 min-h-0 overflow-y-auto pr-1 max-h-[45vh] sm:max-h-[520px] lg:max-h-[600px]">
                        <div id="appointments-list" data-queue-list="1" class="manual-mode pb-2">
                            @forelse($appointments as $appointment)
                                @php
                                    $isLate = $appointment->starts_at < now() && $appointment->status !== 'completed';
                                    $isSelected = session('selected_appointment') === $appointment->id;
                                    $doctorName = optional(optional($appointment->calendar)->doctor)->user->name_full
                                        ?? optional(optional($appointment->calendar)->doctor)->user->name
                                        ?? 'N/A';
                                    $typeName = $appointment->type->name ?? 'Tipo nao informado';
                                    $statusClasses = [
                                        'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                        'rescheduled' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                                        'confirmed' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                                        'arrived' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                        'in_service' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'attended' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                        'completed' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                        'canceled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                        'no_show' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                    ];
                                    $statusClass = $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200';
                                @endphp

                                <div
                                    @class([
                                        'day-queue-item',
                                        'appointment-item',
                                        'w-full',
                                        'px-4',
                                        'py-3',
                                        'transition-colors',
                                        'duration-150',
                                        'active-item' => $isSelected,
                                        'is-late' => $isLate,
                                        'bg-emerald-50/60 border border-emerald-100 hover:bg-emerald-100/60 hover:border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800/40 dark:hover:bg-emerald-900/30 dark:hover:border-emerald-700/50' => !$isSelected && !$isLate,
                                        'border-b border-gray-100 dark:border-gray-700' => $isSelected || $isLate,
                                    ])
                                     data-queue-item="1"
                                     data-appointment-id="{{ $appointment->id }}"
                                     data-start-at="{{ $appointment->starts_at?->toIso8601String() }}"
                                     data-status="{{ strtolower((string) $appointment->status) }}"
                                     data-paciente="{{ mb_strtolower((string) ($appointment->patient->full_name ?? '')) }}"
                                     data-medico="{{ mb_strtolower((string) $doctorName) }}"
                                     data-tipo="{{ mb_strtolower((string) $typeName) }}"
                                     data-queue-position="{{ $appointment->queue_position ?? '' }}">
                                    <div class="flex items-start gap-2">
                                        <button type="button"
                                                class="min-w-0 flex-1 text-left"
                                                data-open-details="1"
                                                data-appointment-id="{{ $appointment->id }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100" data-role="item-title">
                                                        {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->patient->full_name ?? 'N/A' }}
                                                    </p>
                                                    <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400" data-role="item-type">
                                                        {{ $typeName }}
                                                    </p>
                                                </div>
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}" data-role="item-status-badge">
                                                    {{ $appointment->status_translated }}
                                                </span>
                                            </div>
                                        </button>

                                        <button type="button"
                                                class="appointment-drag-handle mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                                data-queue-handle="1"
                                                aria-label="Reordenar agendamento"
                                                title="Arrastar para reordenar">
                                            <i class="mdi mdi-drag-vertical text-base"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                    <x-icon name="calendar-remove-outline" size="text-4xl" class="mx-auto text-gray-400 dark:text-gray-500" />
                                    <p class="mt-2 text-sm">Nenhum agendamento para este dia.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.medical-appointments.index') }}"
                            class="w-full justify-center">
                            <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                            Voltar para Seleção
                        </x-tailadmin-button>
                    </div>
                </div>
            </section>

            <section class="xl:col-span-8 min-w-0">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Detalhes do Atendimento</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dados clinicos e acoes do agendamento.</p>
                    </div>

                    <div id="appointment-details" class="flex-1 overflow-y-auto max-h-[65vh] p-5">
                        <div class="h-full min-h-[220px] flex items-center justify-center text-center text-gray-500 dark:text-gray-400">
                            <div>
                                <x-icon name="information-outline" size="text-4xl" class="mx-auto text-gray-400 dark:text-gray-500" />
                                <p class="mt-2 text-sm">Selecione um agendamento para visualizar os detalhes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div
        id="medical-status-modal"
        class="fixed inset-0 z-[999999] hidden items-center justify-center p-2 sm:p-4"
        role="dialog"
        aria-modal="true"
        aria-hidden="true"
        aria-labelledby="medical-status-modal-label"
    >
        <div class="absolute inset-0 bg-black/40" data-medical-status-close></div>

        <div class="relative z-10 mx-auto w-[95vw] max-w-[640px] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-boxdark">
            <button
                type="button"
                class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                data-medical-status-close
                title="Fechar"
                aria-label="Fechar"
            >
                <x-icon name="close" size="text-base" />
            </button>

            <form id="medical-status-form" novalidate>
                <input type="hidden" id="medical-status-appointment-id" value="" />

                <div class="shrink-0 border-b border-gray-200 p-5 dark:border-gray-800">
                    <h5 id="medical-status-modal-label" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Alterar Status
                    </h5>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Escolha o novo status e informe os dados obrigatorios quando necessario.
                    </p>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label for="medical-status-select" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Novo status</label>
                        <select id="medical-status-select" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" required>
                            <option value="" disabled selected>Selecione o status</option>
                            <option value="CHEGOU">Chegou</option>
                            <option value="EM_ATENDIMENTO">Em atendimento</option>
                            <option value="CONCLUIDO">Concluído</option>
                            <option value="NAO_COMPARECEU">Não compareceu</option>
                            <option value="CANCELADO">Cancelado</option>
                            <option value="REMARCADO">Remarcado</option>
                        </select>
                    </div>

                    <div id="medical-status-note-wrapper" class="hidden">
                        <label for="medical-status-note" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Observacao / Motivo</label>
                        <textarea id="medical-status-note" rows="3" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="Descreva o motivo"></textarea>
                    </div>

                    <div id="medical-status-reschedule-wrapper" class="hidden">
                        <label for="medical-status-reschedule-at" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nova data e hora</label>
                        <input type="datetime-local" id="medical-status-reschedule-at" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    </div>

                    <div id="medical-status-error" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"></div>
                </div>

                <div class="shrink-0 border-t border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex justify-end gap-2">
                        <x-tailadmin-button type="button" variant="secondary" size="sm" data-medical-status-close>
                            Cancelar
                        </x-tailadmin-button>
                        <x-tailadmin-button type="submit" variant="primary" size="sm" id="medical-status-submit">
                            Salvar Status
                        </x-tailadmin-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div
        id="form-response-modal"
        class="fixed inset-0 z-999999 hidden items-center justify-center p-4 sm:p-5"
        role="dialog"
        aria-modal="true"
        aria-hidden="true"
        aria-labelledby="form-response-modal-label"
    >
        <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[2px]" data-form-response-modal-close></div>

        <div
            id="form-response-modal-dialog"
            class="relative mx-auto flex w-full max-w-4xl max-h-[80vh] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
            style="max-width: 56rem; max-height: 80vh;"
        >
            <button
                type="button"
                class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                data-form-response-modal-close
                title="Fechar"
                aria-label="Fechar"
            >
                <x-icon name="close" size="text-base" />
            </button>

            <div class="shrink-0 border-b border-gray-200 p-5 dark:border-gray-800">
                <h5 id="form-response-modal-label" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Formulario Respondido
                </h5>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Visualizacao das respostas enviadas pelo paciente.
                </p>
            </div>

            <div id="form-response-modal-body" class="min-h-0 flex-1 overflow-y-auto p-5">
                <div class="flex items-center justify-center py-12 text-center text-gray-500 dark:text-gray-400">
                    <div>
                        <div class="mx-auto inline-flex h-10 w-10 animate-spin rounded-full border-2 border-gray-200 border-t-blue-500"></div>
                        <p class="mt-3 text-sm">Carregando formulario...</p>
                    </div>
                </div>
            </div>

            <div class="shrink-0 border-t border-gray-200 p-4 dark:border-gray-800">
                <div class="flex justify-end">
                    <x-tailadmin-button type="button" variant="secondary" size="sm" data-form-response-modal-close>
                        <x-icon name="close" size="text-sm" class="mr-2" />
                        Fechar
                    </x-tailadmin-button>
                </div>
            </div>
        </div>
    </div>
@endsection
