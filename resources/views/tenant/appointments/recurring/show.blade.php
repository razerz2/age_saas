@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Agendamento Recorrente')
@section('page', 'appointments')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Detalhes do Agendamento Recorrente</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Agendamentos Recorrentes</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex items-center justify-end gap-3 flex-nowrap">
                <a href="{{ workspace_route('tenant.recurring-appointments.edit', ['id' => $recurringAppointment->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 hover:bg-amber-200 text-amber-700 dark:bg-amber-900 dark:text-amber-300 dark:hover:bg-amber-800 text-sm font-medium rounded-md transition-colors">
                    Editar
                </a>
                @if($recurringAppointment->active)
                    <a href="{{ workspace_route('tenant.recurring-appointments.cancel', ['id' => $recurringAppointment->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors">
                        Cancelar
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Informações Básicas</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Paciente</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recurringAppointment->patient->full_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Médico</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recurringAppointment->doctor->user->name_full ?? $recurringAppointment->doctor->user->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Tipo de Consulta</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recurringAppointment->appointmentType->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Data Inicial</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recurringAppointment->start_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Tipo de Término</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                @if($recurringAppointment->end_type === 'none')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">Sem limite (infinito)</span>
                                @elseif($recurringAppointment->end_type === 'total_sessions')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200">{{ $recurringAppointment->total_sessions }} sessões</span>
                                @elseif($recurringAppointment->end_type === 'date')
                                    {{ $recurringAppointment->end_date->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                @if($recurringAppointment->active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">Ativo</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">Cancelado</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-md p-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Sessões Geradas</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recurringAppointment->getGeneratedSessionsCount() }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Regras de Recorrência</h3>
                    <div class="space-y-3">
                        @foreach($recurringAppointment->rules as $rule)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-3">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($rule->weekday) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $rule->start_time }} - {{ $rule->end_time }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    Frequência:
                                    @if($rule->frequency === 'weekly') Semanal
                                    @elseif($rule->frequency === 'biweekly') Quinzenal
                                    @elseif($rule->frequency === 'monthly') Mensal
                                    @endif
                                    @if($rule->interval > 1)
                                        (Intervalo: {{ $rule->interval }})
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($recurringAppointment->appointments->count() > 0)
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Sessões Geradas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data/Hora Início</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data/Hora Fim</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recurringAppointment->appointments as $appointment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $appointment->starts_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $appointment->ends_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $appointment->status_translated }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
