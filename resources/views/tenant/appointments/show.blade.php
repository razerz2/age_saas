@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Agendamento')

@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Detalhes do Agendamento
        </h1>
        <nav class="flex mt-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ workspace_route('tenant.appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Agendamentos</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500">Detalhes</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informações do Agendamento
                </h2>
                <div class="flex gap-2">
                    <a href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}" class="px-3 py-1.5 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <a href="{{ workspace_route('tenant.appointments.index') }}" class="px-3 py-1.5 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Status Badge e Modo -->
            <div class="mb-6 flex gap-2">
                @php
                    $statusBadges = [
                        'pending' => ['bg-yellow-100', 'text-yellow-800', 'mdi-clock-outline'],
                        'confirmed' => ['bg-green-100', 'text-green-800', 'mdi-check-circle'],
                        'cancelled' => ['bg-red-100', 'text-red-800', 'mdi-cancel'],
                        'completed' => ['bg-blue-100', 'text-blue-800', 'mdi-check-all'],
                    ];
                    $statusInfo = $statusBadges[$appointment->status] ?? ['bg-gray-100', 'text-gray-800', 'mdi-help-circle'];
                @endphp
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusInfo[0] }} {{ $statusInfo[1] }}">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 0-1.653-.626-1.653-1.653 0-1.026.393-1.026.926 0 1.653.626 1.653 1.653.326 0 .626-.393 1.026-.926 1.653-1.653 1.653zm-4.908 4.908c.3-.921 0-1.653-.626-1.653-1.653 0-1.026.393-1.026.926 0 1.653.626 1.653 1.653.326 0 .626-.393 1.026-.926 1.653-1.653 1.653zm4.908 4.908c.3-.921 0-1.653-.626-1.653-1.653 0-1.026.393-1.026.926 0 1.653.626 1.653 1.653.326 0 .626-.393 1.026-.926 1.653-1.653 1.653z"></path>
                    </svg>
                    {{ $appointment->status_translated }}
                </span>
                @if($appointment->appointment_mode === 'online')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                            <path d="M8 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H8a2 2 0 01-2-2V6z"></path>
                        </svg>
                        Online
                    </span>
                @else
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Presencial
                    </span>
                @endif
            </div>

            <!-- Informações do Agendamento -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informações do Agendamento
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">ID</label>
                        <p class="text-gray-900 font-medium">{{ $appointment->id }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Paciente</label>
                        @if($appointment->patient)
                            <p class="text-gray-900 font-medium">
                                <a href="{{ workspace_route('tenant.patients.show', $appointment->patient->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $appointment->patient->full_name }}
                                </a>
                            </p>
                        @else
                            <p class="text-gray-400">N/A</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Médico</label>
                        @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                            <p class="text-gray-900 font-medium">
                                <a href="{{ workspace_route('tenant.doctors.show', $appointment->calendar->doctor->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $appointment->calendar->doctor->user->name }}
                                </a>
                            </p>
                        @else
                            <p class="text-gray-400">N/A</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Calendário</label>
                        <p class="text-gray-900 font-medium">{{ $appointment->calendar->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Tipo de Consulta</label>
                        <p class="text-gray-900 font-medium">{{ $appointment->type->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Especialidade</label>
                        <p class="text-gray-900 font-medium">{{ $appointment->specialty->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Data e Hora -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Data e Hora
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Início</label>
                        <p class="text-gray-900 font-medium text-lg">
                            {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Fim</label>
                        <p class="text-gray-900 font-medium text-lg">
                            {{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
                @if($appointment->starts_at && $appointment->ends_at)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <label class="text-sm font-medium text-gray-500 mb-1 block">Duração</label>
                                <p class="text-gray-900 font-medium text-lg">{{ $appointment->starts_at->diffInMinutes($appointment->ends_at) }} minutos</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Observações -->
            @if($appointment->notes)
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Observações
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900">{{ $appointment->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Informações Adicionais -->
            <div class="border-t border-gray-200 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Criado em</label>
                        <p class="text-gray-900 font-medium">{{ $appointment->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 mb-1 block">Atualizado em</label>
                        <p class="text-gray-900 font-medium">{{ $appointment->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-end gap-3 flex-nowrap">
                    @if($appointment->appointment_mode === 'online')
                        <a href="{{ workspace_route('tenant.online-appointments.show', $appointment->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0021 8.618v6.764a1 1 0 01-1.447.894L15 14V10z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 18h16"></path>
                            </svg>
                            Instruções Online
                        </a>
                    @endif
                    
                    @php
                        $tenant = \App\Models\Platform\Tenant::current();
                    @endphp
                    @if($form && $tenant)
                        @if(isset($formResponse) && $formResponse)
                            <!-- Se já existe resposta, mostrar botão para visualizar -->
                            <a href="{{ workspace_route('tenant.responses.show', ['id' => $formResponse->id]) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Ver Formulário
                            </a>
                        @else
                            <!-- Se não existe resposta, mostrar botão para responder -->
                            <a href="{{ tenant_route($tenant, 'public.form.response.create', ['form' => $form->id, 'appointment' => $appointment->id]) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Responder Formulário
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

