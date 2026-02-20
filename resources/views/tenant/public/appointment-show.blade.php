@extends('layouts.tailadmin.public')

@section('title', 'Detalhes do Agendamento — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-8">
            <div class="pt-10 pb-6 sm:pt-12 sm:pb-8 lg:pt-14 text-center">
                <div class="mx-auto mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="mdi mdi-calendar-check text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Detalhes do Agendamento</h1>
                <p class="mt-2 text-sm text-slate-600">Confira os dados do seu agendamento.</p>
            </div>

            @php
                $status = $appointment->status ?? null;
                $statusText = $appointment->status_translated ?? ($status ?: '—');
                $statusClasses = 'bg-slate-50 text-slate-700 ring-slate-200';
                if ($status === 'scheduled') $statusClasses = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                if ($status === 'attended') $statusClasses = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                if ($status === 'canceled' || $status === 'no_show') $statusClasses = 'bg-red-50 text-red-700 ring-red-200';
                if ($status === 'rescheduled') $statusClasses = 'bg-amber-50 text-amber-800 ring-amber-200';
            @endphp

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="px-6 py-5 text-center">
                    <div class="mx-auto w-full text-center">
                        <h2 class="text-base font-semibold text-slate-900">Detalhes</h2>
                        <p class="mt-1 text-sm text-slate-600">Informações principais do agendamento.</p>
                        <div class="mt-4 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClasses }}">
                            {{ $statusText }}
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 px-6 py-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-center justify-items-center items-start">
                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paciente</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $appointment->patient->full_name ?? '—' }}</div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Profissional</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                                    {{ $appointment->calendar->doctor->user->name_full ?? $appointment->calendar->doctor->user->name ?? '—' }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Calendário</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $appointment->calendar->name ?? '—' }}</div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo de Consulta</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $appointment->type->name ?? '—' }}</div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Especialidade</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $appointment->specialty->name ?? '—' }}</div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Início</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y \à\s H:i') : '—' }}
                            </div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Término</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y \à\s H:i') : '—' }}
                            </div>
                        </div>

                        <div class="mx-auto w-full max-w-xs text-center">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Agendado em</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $appointment->created_at ? $appointment->created_at->format('d/m/Y \à\s H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                @if($appointment->notes)
                    <div class="border-t border-slate-200 px-6 py-5">
                        <h3 class="text-base font-semibold text-slate-900 text-center sm:text-left">Observações</h3>
                        <div class="mt-3 whitespace-pre-line rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">{{ $appointment->notes }}</div>
                    </div>
                @endif

                @php
                    $form = \App\Models\Tenant\Form::getFormForAppointment($appointment);
                @endphp

                @if($form)
                    <div class="border-t border-slate-200 px-6 py-5">
                        <div class="flex justify-center">
                            <a href="{{ tenant_route($tenant, 'public.form.response.create', ['form' => $form->id, 'appointment' => $appointment->id]) }}" class="btn btn-primary">
                                <i class="mdi mdi-file-document-edit text-lg text-white"></i>
                                Responder Formulário
                            </a>
                        </div>
                    </div>
                @endif

                <div class="border-t border-slate-200 px-6 py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}" class="btn btn-outline">
                            <i class="mdi mdi-arrow-left text-lg text-slate-900"></i>
                            Voltar
                        </a>

                        <a href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}" class="btn btn-primary">
                            <i class="mdi mdi-calendar-plus text-lg text-white"></i>
                            Novo Agendamento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
