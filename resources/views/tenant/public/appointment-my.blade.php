@extends('layouts.tailadmin.public')

@section('title', 'Meus Agendamentos — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8 pt-10 pb-10 sm:pt-12 sm:pb-12 lg:pt-14 lg:pb-14">
            <div class="mb-6 sm:mb-8 text-center">
                <div class="mx-auto flex max-w-3xl flex-col items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-1">
                        <i class="mdi mdi-calendar-multiple text-xl"></i>
                    </span>
                    <div class="w-full">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 text-center">
                            Meus Agendamentos
                        </h1>
                        <p class="mt-1 text-sm text-slate-600 text-center">
                            Confira seus agendamentos e escolha uma ação.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                <span class="font-semibold text-slate-900">Paciente:</span>
                <span>{{ $patient->full_name }}</span>
            </div>

            <div class="mb-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('public.appointment.create', ['slug' => $tenant->subdomain]) }}" class="btn btn-primary w-full sm:w-auto">
                        <i class="mdi mdi-calendar-plus text-base text-white"></i>
                        Novo Agendamento
                    </a>

                    <a href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}" class="btn btn-outline w-full sm:w-auto">
                        <i class="mdi mdi-account-switch text-base text-slate-900"></i>
                        Trocar paciente
                    </a>
                </div>
            </div>

            <div class="mb-6 space-y-3">
                @if (session('success'))
                    <div class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <i class="mdi mdi-check-circle text-base"></i>
                        </span>
                        <div class="flex-1">
                            <p class="font-semibold">Sucesso</p>
                            <p class="mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

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

                @if (session('info'))
                    <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            <i class="mdi mdi-information text-base"></i>
                        </span>
                        <div class="flex-1">
                            <p class="font-semibold">Informação</p>
                            <p class="mt-0.5">{{ session('info') }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('public.appointment.my', ['slug' => $tenant->subdomain]) }}" class="grid grid-cols-1 gap-3 md:grid-cols-3 md:items-end">
                    <div>
                        <label for="sort" class="mb-1 block text-sm font-medium text-slate-700">Ordenar por</label>
                        <select id="sort" name="sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="starts_at" {{ $sort === 'starts_at' ? 'selected' : '' }}>Data do atendimento</option>
                            <option value="created_at" {{ $sort === 'created_at' ? 'selected' : '' }}>Data de criação</option>
                            <option value="status" {{ $sort === 'status' ? 'selected' : '' }}>Status</option>
                        </select>
                    </div>

                    <div>
                        <label for="direction" class="mb-1 block text-sm font-medium text-slate-700">Direção</label>
                        <select id="direction" name="direction" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>Mais recentes primeiro</option>
                            <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>Mais antigos primeiro</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary w-full md:w-auto">
                            <i class="mdi mdi-filter-variant text-base text-white"></i>
                            Aplicar
                        </button>
                    </div>
                </form>
            </div>

            @if ($appointments->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <p class="text-slate-700">Nenhum agendamento encontrado.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($appointments as $appointment)
                        @php
                            $statusClasses = 'bg-slate-50 text-slate-700 ring-slate-200';

                            if (in_array($appointment->status, ['scheduled', 'confirmed', 'attended', 'completed'], true)) {
                                $statusClasses = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                            }

                            if ($appointment->status === 'pending_confirmation') {
                                $statusClasses = 'bg-yellow-50 text-yellow-700 ring-yellow-200';
                            }

                            if ($appointment->status === 'rescheduled') {
                                $statusClasses = 'bg-amber-50 text-amber-800 ring-amber-200';
                            }

                            if (in_array($appointment->status, ['canceled', 'cancelled', 'no_show'], true)) {
                                $statusClasses = 'bg-red-50 text-red-700 ring-red-200';
                            }

                            if ($appointment->status === 'expired') {
                                $statusClasses = 'bg-orange-50 text-orange-700 ring-orange-200';
                            }

                            $modeText = '—';
                            if ($appointment->appointment_mode === 'presencial') $modeText = 'Presencial';
                            if ($appointment->appointment_mode === 'online') $modeText = 'Online';

                            $showUrlParams = [
                                'slug' => $tenant->subdomain,
                                'appointment_id' => $appointment->id,
                            ];

                            if (!empty($appointment->confirmation_token)) {
                                $showUrlParams['token'] = $appointment->confirmation_token;
                            }

                            $canReschedule = in_array($appointment->status, ['scheduled', 'rescheduled', 'pending_confirmation', 'confirmed'], true);
                            $canCancel = !empty($appointment->confirmation_token) && $canReschedule;
                        @endphp

                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
                                <h2 class="text-base font-semibold text-slate-900">Agendamento</h2>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClasses }}">
                                    {{ $appointment->status_translated }}
                                </span>
                            </div>

                            <div class="p-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-slate-500">Data e hora de início</p>
                                        <p class="font-semibold text-slate-900 mt-1">{{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y \à\s H:i') : '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500">Profissional</p>
                                        <p class="font-semibold text-slate-900 mt-1">{{ $appointment->calendar?->doctor?->user?->name_full ?? $appointment->calendar?->doctor?->user?->name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500">Especialidade</p>
                                        <p class="font-semibold text-slate-900 mt-1">{{ $appointment->specialty->name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500">Tipo de consulta</p>
                                        <p class="font-semibold text-slate-900 mt-1">{{ $appointment->type->name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500">Modo</p>
                                        <p class="font-semibold text-slate-900 mt-1">{{ $modeText }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500">Criado em</p>
                                        <p class="font-semibold text-slate-900 mt-1">{{ $appointment->created_at ? $appointment->created_at->format('d/m/Y \à\s H:i') : '—' }}</p>
                                    </div>
                                </div>

                                @if(in_array($appointment->status, ['canceled', 'cancelled'], true) && $appointment->canceled_at)
                                    <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800">
                                        <p><span class="font-semibold">Cancelado em:</span> {{ $appointment->canceled_at->format('d/m/Y \à\s H:i') }}</p>
                                    </div>
                                @endif

                                @if(!empty($appointment->cancellation_reason))
                                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Motivo do cancelamento</p>
                                        <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $appointment->cancellation_reason }}</p>
                                    </div>
                                @endif

                                @if(!empty($appointment->notes) && mb_strlen(trim($appointment->notes)) > 2)
                                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Observações</p>
                                        <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $appointment->notes }}</p>
                                    </div>
                                @endif

                                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                    <a href="{{ route('public.appointment.show', $showUrlParams) }}" class="btn btn-outline">
                                        <i class="mdi mdi-eye-outline text-base text-slate-900"></i>
                                        Ver detalhes
                                    </a>

                                    @if($canReschedule)
                                        <button
                                            type="button"
                                            class="btn btn-outline js-open-reschedule-confirm"
                                            data-reschedule-url="{{ route('public.appointment.create', ['slug' => $tenant->subdomain, 'reschedule_from' => $appointment->id]) }}"
                                        >
                                            <i class="mdi mdi-calendar-refresh text-base text-slate-900"></i>
                                            Reagendar
                                        </button>
                                    @endif

                                    @if($canCancel)
                                        <form id="cancel-appointment-form-{{ $appointment->id }}" method="POST" action="{{ route('public.appointment.cancel', ['slug' => $tenant->subdomain, 'token' => $appointment->confirmation_token]) }}" class="inline-flex">
                                            @csrf
                                            <button
                                                type="button"
                                                class="btn btn-outline js-open-cancel-confirm"
                                                data-cancel-form-id="cancel-appointment-form-{{ $appointment->id }}"
                                            >
                                                <i class="mdi mdi-close-circle-outline text-base text-slate-900"></i>
                                                Cancelar
                                            </button>
                                        </form>
                                    @elseif(empty($appointment->confirmation_token))
                                        <button type="button" class="btn btn-outline opacity-60 cursor-not-allowed" disabled>
                                            <i class="mdi mdi-close-circle-outline text-base text-slate-900"></i>
                                            Cancelamento indisponível
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            var rescheduleButton = event.target.closest('.js-open-reschedule-confirm');
            if (rescheduleButton) {
                var targetUrl = rescheduleButton.getAttribute('data-reschedule-url');

                if (!targetUrl || typeof window.confirmAction !== 'function') {
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                    return;
                }

                window.confirmAction({
                    type: 'warning',
                    title: 'Confirmar reagendamento',
                    message: 'Você será direcionado para escolher uma nova data e horário. O agendamento atual só será alterado após a confirmação do novo horário.',
                    confirmText: 'Continuar',
                    cancelText: 'Voltar',
                    allowOutsideClose: true,
                    onConfirm: function () {
                        window.location.href = targetUrl;
                    }
                });

                return;
            }

            var cancelButton = event.target.closest('.js-open-cancel-confirm');
            if (cancelButton) {
                var formId = cancelButton.getAttribute('data-cancel-form-id');
                var form = formId ? document.getElementById(formId) : null;

                if (!form || typeof window.confirmAction !== 'function') {
                    if (form) {
                        form.submit();
                    }
                    return;
                }

                window.confirmAction({
                    type: 'error',
                    title: 'Cancelar agendamento',
                    message: 'Tem certeza que deseja cancelar este agendamento? Esta ação liberará o horário na agenda.',
                    confirmText: 'Sim, cancelar',
                    cancelText: 'Voltar',
                    allowOutsideClose: true,
                    onConfirm: function () {
                        form.submit();
                    }
                });
            }
        });
    </script>
@endpush
