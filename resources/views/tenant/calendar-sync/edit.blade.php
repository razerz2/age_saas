@extends('layouts.tailadmin.app')

@section('title', 'Editar Estado de Sincronização')
@section('page', 'calendars')

@section('content')
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Estado de Sincronização</h1>
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
                                <a href="{{ workspace_route('tenant.calendar-sync.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Sincronização de Calendário</a>
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
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                <x-icon name="pencil-outline" class="w-6 h-6 mr-2 text-blue-600" />
                Editar Estado de Sincronização
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Atualize as informações do estado de sincronização abaixo.</p>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <x-icon name="alert-circle-outline" class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" />
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Ops! Verifique os erros abaixo:</h3>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-300">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form class="space-y-8" action="{{ workspace_route('tenant.calendar-sync.update', $calendarSyncState->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Informações Básicas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Agendamento</label>
                            <select name="appointment_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" disabled>
                                <option value="{{ $calendarSyncState->appointment_id }}">
                                    @if($calendarSyncState->appointment)
                                        {{ $calendarSyncState->appointment->patient->full_name ?? 'N/A' }} -
                                        {{ $calendarSyncState->appointment->starts_at ? $calendarSyncState->appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}
                                        @if($calendarSyncState->appointment->calendar && $calendarSyncState->appointment->calendar->doctor)
                                            - Dr(a). {{ $calendarSyncState->appointment->calendar->doctor->user->name ?? 'N/A' }}
                                        @endif
                                    @else
                                        Agendamento #{{ $calendarSyncState->appointment_id }}
                                    @endif
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">O agendamento não pode ser alterado após a criação.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Provedor <span class="text-red-500">*</span>
                            </label>
                            <select name="provider" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('provider') border-red-500 @enderror" required>
                                <option value="">Selecione um provedor</option>
                                <option value="google" {{ old('provider', $calendarSyncState->provider) == 'google' ? 'selected' : '' }}>Google Calendar</option>
                                <option value="apple" {{ old('provider', $calendarSyncState->provider) == 'apple' ? 'selected' : '' }}>Apple Calendar</option>
                            </select>
                            @error('provider')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="sync" class="w-5 h-5 mr-2 text-blue-600" />
                        Dados de Sincronização
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ID Evento Externo</label>
                            <input type="text"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('external_event_id') border-red-500 @enderror"
                                   name="external_event_id"
                                   value="{{ old('external_event_id', $calendarSyncState->external_event_id) }}"
                                   placeholder="ID do evento no calendário externo">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ID do evento no calendário do provedor.</p>
                            @error('external_event_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Última Sincronização</label>
                            <input type="datetime-local"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('last_sync_at') border-red-500 @enderror"
                                   name="last_sync_at"
                                   value="{{ old('last_sync_at', $calendarSyncState->last_sync_at ? $calendarSyncState->last_sync_at->format('Y-m-d\TH:i') : '') }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data e hora da última sincronização.</p>
                            @error('last_sync_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ workspace_route('tenant.calendar-sync.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-5 h-5 mr-2" />
                        Atualizar Estado de Sincronização
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
