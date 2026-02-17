@extends('layouts.tailadmin.public')

@section('title', 'Detalhes do Agendamento — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')


@section('content')
    <div class="page-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card details-card">
                        <div class="details-header">
                            <i class="mdi mdi-calendar-clock" style="font-size: 48px; margin-bottom: 1rem;"></i>
                            <h2>Detalhes do Agendamento</h2>
                        </div>
                        <div class="details-body">
                            
                            <div class="detail-row">
                                <div class="detail-label">Paciente</div>
                                <div class="detail-value">{{ $appointment->patient->full_name ?? 'N/A' }}</div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label">Profissional</div>
                                <div class="detail-value">
                                    @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                                        {{ $appointment->calendar->doctor->user->name }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label">Calendário</div>
                                <div class="detail-value">{{ $appointment->calendar->name ?? 'N/A' }}</div>
                            </div>

                            @if($appointment->type)
                            <div class="detail-row">
                                <div class="detail-label">Tipo de Consulta</div>
                                <div class="detail-value">{{ $appointment->type->name }}</div>
                            </div>
                            @endif

                            @if($appointment->specialty)
                            <div class="detail-row">
                                <div class="detail-label">Especialidade</div>
                                <div class="detail-value">{{ $appointment->specialty->name }}</div>
                            </div>
                            @endif

                            <div class="detail-row">
                                <div class="detail-label">Data e Hora de Início</div>
                                <div class="detail-value">
                                    {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y \à\s H:i') : 'N/A' }}
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label">Data e Hora de Término</div>
                                <div class="detail-value">
                                    {{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y \à\s H:i') : 'N/A' }}
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    @php
                                        $statusClass = 'badge-scheduled';
                                        if($appointment->status == 'canceled') {
                                            $statusClass = 'badge-cancelled';
                                        } elseif($appointment->status == 'attended') {
                                            $statusClass = 'badge-completed';
                                        } elseif($appointment->status == 'rescheduled') {
                                            $statusClass = 'badge-rescheduled';
                                        } elseif($appointment->status == 'no_show') {
                                            $statusClass = 'badge-cancelled';
                                        }
                                    @endphp
                                    <span class="badge-status {{ $statusClass }}">
                                        {{ $appointment->status_translated }}
                                    </span>
                                </div>
                            </div>

                            @if($appointment->notes)
                            <div class="detail-row">
                                <div class="detail-label">Observações</div>
                                <div class="detail-value">{{ $appointment->notes }}</div>
                            </div>
                            @endif

                            <div class="detail-row">
                                <div class="detail-label">Agendado em</div>
                                <div class="detail-value">
                                    {{ $appointment->created_at ? $appointment->created_at->format('d/m/Y \à\s H:i') : 'N/A' }}
                                </div>
                            </div>

                            @php
                                $form = \App\Models\Tenant\Form::getFormForAppointment($appointment);
                            @endphp

                            @if($form)
                                <div class="mt-4 flex justify-center">
                                    <x-tailadmin-button variant="primary" size="lg" href="{{ tenant_route($tenant, 'public.form.response.create', ['form' => $form->id, 'appointment' => $appointment->id]) }}">
                                        <i class="mdi mdi-file-document-edit"></i>
                                        Responder Formulário
                                    </x-tailadmin-button>
                                </div>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                                <x-tailadmin-button variant="primary" size="md" href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}">
                                    <i class="mdi mdi-calendar-plus"></i>
                                    Novo Agendamento
                                </x-tailadmin-button>
                                @if(session('last_appointment_id'))
                                    <x-tailadmin-button variant="secondary" size="md" href="{{ route('public.appointment.success', ['slug' => $tenant->subdomain, 'appointment_id' => session('last_appointment_id')]) }}"
                                        class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                        <i class="mdi mdi-arrow-left"></i>
                                        Voltar
                                    </x-tailadmin-button>
                                @else
                                    <x-tailadmin-button variant="secondary" size="md" href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}"
                                        class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                        <i class="mdi mdi-arrow-left"></i>
                                        Voltar
                                    </x-tailadmin-button>
                                @endif
                            </div>

                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    © {{ date('Y') }} {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}. Todos os direitos reservados.
                                </small>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

