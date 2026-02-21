@extends('layouts.tailadmin.app')

@section('title', 'Instruções de Consulta Online')
@section('page', 'online_appointments')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Instruções de Consulta Online</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.online-appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Consultas Online</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Instruções</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-8">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configurar Instruções</h2>
            </div>

            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4">
                <h3 class="text-sm font-semibold mb-2">Informações da Consulta</h3>
                <p class="text-sm"><strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                <p class="text-sm"><strong>Data/Hora:</strong> {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</p>
                @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                    <p class="text-sm"><strong>Médico:</strong> {{ $appointment->calendar->doctor->user->name }}</p>
                @endif
            </div>

            <form class="space-y-6" action="{{ workspace_route('tenant.online-appointments.save', $appointment->id) }}" method="POST">
                @csrf

                <div>
                    <label for="meeting_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link da Reunião</label>
                    <input type="url"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('meeting_link') border-red-500 @enderror"
                           id="meeting_link"
                           name="meeting_link"
                           value="{{ old('meeting_link', $appointment->onlineInstructions->meeting_link ?? '') }}"
                           placeholder="https://meet.google.com/xxx-xxxx-xxx">
                    @error('meeting_link')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Link da plataforma de videoconferência (Zoom, Google Meet, etc.)</p>
                </div>

                <div>
                    <label for="meeting_app" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aplicativo</label>
                    <input type="text"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('meeting_app') border-red-500 @enderror"
                           id="meeting_app"
                           name="meeting_app"
                           value="{{ old('meeting_app', $appointment->onlineInstructions->meeting_app ?? '') }}"
                           placeholder="Zoom, Google Meet, Microsoft Teams, etc.">
                    @error('meeting_app')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="general_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instruções Gerais</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('general_instructions') border-red-500 @enderror"
                              id="general_instructions"
                              name="general_instructions"
                              rows="4"
                              placeholder="Instruções gerais para o paciente...">{{ old('general_instructions', $appointment->onlineInstructions->general_instructions ?? '') }}</textarea>
                    @error('general_instructions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="patient_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações para o Paciente</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('patient_instructions') border-red-500 @enderror"
                              id="patient_instructions"
                              name="patient_instructions"
                              rows="4"
                              placeholder="Observações específicas para o paciente...">{{ old('patient_instructions', $appointment->onlineInstructions->patient_instructions ?? '') }}</textarea>
                    @error('patient_instructions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 flex-nowrap pt-2">
                    <a href="{{ workspace_route('tenant.online-appointments.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-4 h-4 mr-2" />
                        Salvar Instruções
                    </button>
                </div>
            </form>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Enviar Instruções ao Paciente</h3>

                @if(!$canSendEmail && !$canSendWhatsapp)
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-md p-4">
                        <strong>Atenção:</strong> Nenhum meio de envio está configurado.
                        Configure as notificações em <a href="{{ workspace_route('tenant.settings.index') }}" class="underline">Configurações</a>.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($canSendEmail)
                            <div>
                                <form action="{{ workspace_route('tenant.online-appointments.send-email', $appointment->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors"
                                            @if(!$appointment->patient->email) disabled title="Paciente não possui email cadastrado" @endif>
                                        Enviar por Email
                                    </button>
                                </form>
                                @if($appointment->onlineInstructions && $appointment->onlineInstructions->sent_by_email_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Último envio: {{ $appointment->onlineInstructions->sent_by_email_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if($canSendWhatsapp)
                            <div>
                                <form action="{{ workspace_route('tenant.online-appointments.send-whatsapp', $appointment->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors"
                                            @if(!$appointment->patient->phone) disabled title="Paciente não possui telefone cadastrado" @endif>
                                        Enviar por WhatsApp
                                    </button>
                                </form>
                                @if($appointment->onlineInstructions && $appointment->onlineInstructions->sent_by_whatsapp_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Último envio: {{ $appointment->onlineInstructions->sent_by_whatsapp_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ workspace_route('tenant.online-appointments.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Voltar
                    </a>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
