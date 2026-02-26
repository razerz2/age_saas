@if($appointment)
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/20">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Paciente</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Nome:</span> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Telefone:</span> {{ $appointment->patient->phone ?? 'N/A' }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Email:</span> {{ $appointment->patient->email ?? 'N/A' }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">CPF:</span> {{ $appointment->patient->cpf ?? 'N/A' }}</p>
                <p class="text-gray-700 dark:text-gray-300 md:col-span-2"><span class="font-medium text-gray-900 dark:text-gray-100">Data de Nascimento:</span>
                    @if($appointment->patient->birth_date)
                        {{ \Carbon\Carbon::parse($appointment->patient->birth_date)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/20">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Consulta</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Tipo:</span> {{ $appointment->type->name ?? 'N/A' }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Médico:</span>
                    @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                        {{ $appointment->calendar->doctor->user->name_full ?? $appointment->calendar->doctor->user->name ?? 'N/A' }}
                    @else
                        N/A
                    @endif
                </p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Especialidade:</span> {{ $appointment->specialty->name ?? 'N/A' }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Horário:</span> {{ $appointment->starts_at->format('d/m/Y H:i') }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium text-gray-900 dark:text-gray-100">Duração:</span>
                    @if($appointment->type && $appointment->type->duration_min)
                        {{ $appointment->type->duration_min }} minutos
                    @else
                        N/A
                    @endif
                </p>
                <p class="text-gray-700 dark:text-gray-300">
                    <span class="font-medium text-gray-900 dark:text-gray-100">Status:</span>
                    @php
                        $statusClasses = [
                            'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'rescheduled' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                            'confirmed' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                            'arrived' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            'in_service' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'attended' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'completed' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                            'canceled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                        ];
                        $statusClass = $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $appointment->status_translated }}</span>
                </p>
            </div>
        </section>

        @if($appointment->notes)
            <section class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/20">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">Observações</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $appointment->notes }}</p>
            </section>
        @endif

        @if(isset($form) && $form)
            <section class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/20">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p><span class="font-medium text-gray-900 dark:text-gray-100">Formulário:</span> {{ $form->name }}</p>
                        @if(isset($formResponse) && $formResponse)
                            <p class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                Respondido
                            </p>
                        @else
                            <p class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                Aguardando resposta
                            </p>
                        @endif
                    </div>

                    @if(isset($formResponse) && $formResponse)
                        <x-tailadmin-button type="button" variant="secondary" size="sm"
                            data-medical-action="view-form-response" data-appointment-id="{{ $appointment->id }}">
                            <x-icon name="eye-outline" size="text-sm" class="mr-2" />
                            Visualizar Resposta
                        </x-tailadmin-button>
                    @endif
                </div>
            </section>
        @endif

        <section>
            <div class="flex flex-wrap gap-2">
                @if($appointment->status === 'scheduled' || $appointment->status === 'confirmed')
                    <x-tailadmin-button
                        type="button"
                        variant="secondary"
                        size="md"
                        class="border-amber-300 bg-amber-100 text-amber-900 hover:bg-amber-200 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200 dark:hover:bg-amber-900/50"
                        title="Marcar paciente como chegou"
                        data-medical-action="update-status"
                        data-appointment-id="{{ $appointment->id }}"
                        data-status="attended"
                    >
                        <x-icon name="check-circle-outline" size="text-sm" class="mr-1" />
                        Paciente Chegou
                    </x-tailadmin-button>
                @endif

                @if($appointment->status === 'arrived')
                    <x-tailadmin-button type="button" variant="success" size="md"
                        data-medical-action="update-status" data-appointment-id="{{ $appointment->id }}" data-status="in_service">
                        Iniciar Atendimento
                    </x-tailadmin-button>
                @endif

                @if($appointment->status === 'in_service')
                    <x-tailadmin-button type="button" variant="primary" size="md"
                        data-medical-action="complete-appointment" data-appointment-id="{{ $appointment->id }}">
                        Finalizar Atendimento
                    </x-tailadmin-button>
                @endif

                @if(!in_array($appointment->status, ['completed', 'cancelled']))
                    <x-tailadmin-button type="button" variant="danger" size="md"
                        data-medical-action="update-status" data-appointment-id="{{ $appointment->id }}" data-status="canceled">
                        Cancelar
                    </x-tailadmin-button>
                @endif
            </div>
        </section>
    </div>
@else
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <div class="flex items-center gap-2 text-red-700 dark:text-red-300 text-sm">
            <x-icon name="alert-circle-outline" size="text-base" />
            Agendamento não encontrado.
        </div>
    </div>
@endif
