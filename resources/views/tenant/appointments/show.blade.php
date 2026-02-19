@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Agendamento')
@section('page', 'appointments')

@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <x-icon name="calendar-outline" class="w-6 h-6 mr-2 text-blue-600" />            Detalhes do Agendamento
        </h1>
        <nav class="flex mt-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 inline-flex items-center"><x-icon name="home-outline" class="w-4 h-4 mr-2" />Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <a href="{{ workspace_route('tenant.appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Agendamentos</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
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
                    <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                    Informações do Agendamento
                </h2>
                <div class="flex gap-2">
                    <a href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}" class="px-3 py-1.5 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                        <x-icon name="pencil-outline" class="w-4 h-4 mr-1" />                        Editar
                    </a>
                    <a href="{{ workspace_route('tenant.appointments.index') }}" class="px-3 py-1.5 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-1" />                        Voltar
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
                    <x-icon name="calendar-check-outline" class="w-4 h-4 mr-1" />                    {{ $appointment->status_translated }}
                </span>
                @if($appointment->appointment_mode === 'online')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <x-icon name="video-outline" class="w-4 h-4 mr-1" />                        Online
                    </span>
                @else
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <x-icon name="map-marker-outline" class="w-4 h-4 mr-1" />                        Presencial
                    </span>
                @endif
            </div>

            <!-- Informações do Agendamento -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
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
                    <x-icon name="clock-outline" class="w-5 h-5 mr-2 text-blue-600" />                    Data e Hora
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
                            <x-icon name="clock-outline" class="w-5 h-5 mr-2 text-blue-600" />                            <div>                                <label class="text-sm font-medium text-gray-500 mb-1 block">Duração</label>
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
                        <x-icon name="note-text-outline" class="w-5 h-5 mr-2 text-blue-600" />
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
                        <a href="{{ workspace_route('tenant.online-appointments.show', $appointment->id) }}" class="btn btn-primary">
                            <x-icon name="video-outline" class="w-4 h-4 mr-2" />
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
                                <x-icon name="eye-outline" class="w-4 h-4 mr-2" />
                                Ver Formulário
                            </a>
                        @else
                            <!-- Se não existe resposta, mostrar botão para responder -->
                            <a href="{{ tenant_route($tenant, 'public.form.response.create', ['form' => $form->id, 'appointment' => $appointment->id]) }}" 
                               target="_blank"
                               class="btn btn-primary">
                                <x-icon name="clipboard-text-outline" class="w-4 h-4 mr-2" />
                                Responder Formulário
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
