@extends('layouts.tailadmin.app')

@section('title', 'Editar Instruções da Consulta Online')
@section('page', 'online_appointments')

@section('content')
    @php
        $instructions = $appointment->onlineInstructions;
    @endphp

    <div class="page-header mb-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Instruções da Consulta Online</h1>
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
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.online-appointments.show', ['appointment' => $appointment->id]) }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Instruções</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex-shrink-0">
                <x-help-button module="online-appointments" />
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-6">
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-200">
                <h3 class="text-sm font-semibold mb-2">Informações da Consulta</h3>
                <p class="text-sm"><strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                <p class="text-sm"><strong>Data/Hora:</strong> {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</p>
                @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                    <p class="text-sm"><strong>Médico:</strong> {{ $appointment->calendar->doctor->user->name }}</p>
                @endif
            </div>

            <form class="space-y-6" action="{{ workspace_route('tenant.online-appointments.save', ['appointment' => $appointment->id]) }}" method="POST">
                @csrf

                <div>
                    <label for="meeting_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link da reunião</label>
                    <input
                        type="url"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('meeting_link') border-red-500 @enderror"
                        id="meeting_link"
                        name="meeting_link"
                        value="{{ old('meeting_link', $instructions->meeting_link ?? '') }}"
                        placeholder="https://meet.google.com/xxx-xxxx-xxx"
                    >
                    @error('meeting_link')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="meeting_app" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aplicativo</label>
                    <input
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('meeting_app') border-red-500 @enderror"
                        id="meeting_app"
                        name="meeting_app"
                        value="{{ old('meeting_app', $instructions->meeting_app ?? '') }}"
                        placeholder="Google Meet, Zoom, Microsoft Teams..."
                    >
                    @error('meeting_app')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="general_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Instruções gerais</label>
                    <textarea
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('general_instructions') border-red-500 @enderror"
                        id="general_instructions"
                        name="general_instructions"
                        rows="4"
                        placeholder="Instruções gerais para o paciente..."
                    >{{ old('general_instructions', $instructions->general_instructions ?? '') }}</textarea>
                    @error('general_instructions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="patient_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações para o paciente</label>
                    <textarea
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('patient_instructions') border-red-500 @enderror"
                        id="patient_instructions"
                        name="patient_instructions"
                        rows="4"
                        placeholder="Observações específicas para o paciente..."
                    >{{ old('patient_instructions', $instructions->patient_instructions ?? '') }}</textarea>
                    @error('patient_instructions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ workspace_route('tenant.online-appointments.show', ['appointment' => $appointment->id]) }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary inline-flex items-center">
                        <x-icon name="content-save-outline" class="w-4 h-4 mr-2" />
                        Salvar instruções
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection