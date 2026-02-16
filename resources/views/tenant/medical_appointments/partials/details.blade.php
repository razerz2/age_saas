@if($appointment)
    <div class="appointment-details">
        {{-- Informações do Paciente --}}
        <div class="mb-4">
            <h5 class="text-primary mb-3">
                <i class="mdi mdi-account-heart me-2"></i>
                Informações do Paciente
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                    <p><strong>Telefone:</strong> {{ $appointment->patient->phone ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $appointment->patient->email ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>CPF:</strong> {{ $appointment->patient->cpf ?? 'N/A' }}</p>
                    <p><strong>Data de Nascimento:</strong> 
                        @if($appointment->patient->birth_date)
                            {{ \Carbon\Carbon::parse($appointment->patient->birth_date)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <hr>

        {{-- Informações da Consulta --}}
        <div class="mb-4">
            <h5 class="text-primary mb-3">
                <i class="mdi mdi-calendar-clock me-2"></i>
                Informações da Consulta
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tipo de Consulta:</strong> {{ $appointment->type->name ?? 'N/A' }}</p>
                    <p><strong>Médico:</strong> 
                        @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                            {{ $appointment->calendar->doctor->user->name_full ?? $appointment->calendar->doctor->user->name ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Especialidade:</strong> {{ $appointment->specialty->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Horário:</strong> {{ $appointment->starts_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Duração:</strong> 
                        @if($appointment->type && $appointment->type->duration_min)
                            {{ $appointment->type->duration_min }} minutos
                        @else
                            N/A
                        @endif
                    </p>
                    <p>
                        <strong>Status:</strong>
                        @php
                            $statusClasses = [
                                'scheduled' => 'badge-primary',
                                'confirmed' => 'badge-info',
                                'arrived' => 'badge-warning',
                                'in_service' => 'badge-success',
                                'completed' => 'badge-secondary',
                                'cancelled' => 'badge-danger',
                            ];
                            $statusClass = $statusClasses[$appointment->status] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $appointment->status_translated }}</span>
                    </p>
                </div>
            </div>
        </div>

        @if($appointment->notes)
            <hr>
            <div class="mb-4">
                <h5 class="text-primary mb-3">
                    <i class="mdi mdi-note-text me-2"></i>
                    Observações
                </h5>
                <p class="text-muted">{{ $appointment->notes }}</p>
            </div>
        @endif

        {{-- Seção: Formulário --}}
        @if(isset($form) && $form)
            <hr>
            <div class="mb-4">
                <h5 class="text-primary mb-3">
                    <i class="mdi mdi-file-document me-2"></i>
                    Formulário
                </h5>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1"><strong>Formulário:</strong> {{ $form->name }}</p>
                        @if(isset($formResponse) && $formResponse)
                            <p class="mb-0">
                                <span class="badge bg-success">
                                    <i class="mdi mdi-check-circle me-1"></i>
                                    Respondido
                                </span>
                                @if($formResponse->submitted_at)
                                    <small class="text-muted ms-2">
                                        em {{ $formResponse->submitted_at->format('d/m/Y H:i') }}
                                    </small>
                                @endif
                            </p>
                        @else
                            <p class="mb-0">
                                <span class="badge bg-warning">
                                    <i class="mdi mdi-clock-outline me-1"></i>
                                    Aguardando Resposta
                                </span>
                            </p>
                        @endif
                    </div>
                        @if(isset($formResponse) && $formResponse)
                            <x-tailadmin-button type="button" variant="secondary" size="md"
                                class="border-info text-info bg-info/10 hover:bg-info/20 dark:border-info/40 dark:text-info dark:hover:bg-info/30"
                                onclick="viewFormResponse('{{ $appointment->id }}')">
                                <i class="mdi mdi-eye"></i>
                                Visualizar Resposta
                            </x-tailadmin-button>
                        @endif
                </div>
            </div>
        @endif

        <hr>

        {{-- Ações --}}
        <div class="flex flex-wrap gap-2">
            @if($appointment->status === 'scheduled' || $appointment->status === 'confirmed')
                <x-tailadmin-button type="button" variant="warning" size="md"
                    onclick="updateStatus('{{ $appointment->id }}', 'arrived')">
                    <i class="mdi mdi-account-check"></i>
                    Paciente Chegou
                </x-tailadmin-button>
            @endif

            @if($appointment->status === 'arrived')
                <x-tailadmin-button type="button" variant="success" size="md"
                    onclick="updateStatus('{{ $appointment->id }}', 'in_service')">
                    <i class="mdi mdi-play-circle"></i>
                    Iniciar Atendimento
                </x-tailadmin-button>
            @endif

            @if($appointment->status === 'in_service')
                <x-tailadmin-button type="button" variant="primary" size="md"
                    onclick="completeAppointment('{{ $appointment->id }}')">
                    <i class="mdi mdi-check-circle"></i>
                    Finalizar Atendimento
                </x-tailadmin-button>
            @endif

            @if(!in_array($appointment->status, ['completed', 'cancelled']))
                <x-tailadmin-button type="button" variant="danger" size="md"
                    onclick="updateStatus('{{ $appointment->id }}', 'cancelled')">
                    <i class="mdi mdi-cancel"></i>
                    Cancelar
                </x-tailadmin-button>
            @endif
        </div>
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="mdi mdi-alert-circle" style="font-size: 3rem;"></i>
        <p class="mt-2 mb-0">Agendamento não encontrado</p>
    </div>
@endif

