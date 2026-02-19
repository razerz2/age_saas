@extends('layouts.tailadmin.app')

@section('title', 'Cancelar Agendamento Recorrente')
@section('page', 'appointments')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Cancelar Agendamento Recorrente</h1>
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
                                <a href="{{ workspace_route('tenant.recurring-appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Agendamentos Recorrentes</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Cancelar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-2xl">
        <div class="p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Confirmar Cancelamento</h2>
            </div>

            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-md p-4">
                <strong>Atenção!</strong> Ao cancelar este agendamento recorrente:
                <ul class="mt-2 list-disc list-inside">
                    <li>Não serão geradas novas sessões</li>
                    <li>Os horários bloqueados serão liberados</li>
                    <li>As sessões já geradas não serão afetadas</li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Informações do Agendamento</h3>
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
                        <span class="text-sm text-gray-600 dark:text-gray-400">Sessões Geradas</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recurringAppointment->getGeneratedSessionsCount() }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ workspace_route('tenant.recurring-appointments.destroy', ['id' => $recurringAppointment->id]) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ workspace_route('tenant.recurring-appointments.show', ['id' => $recurringAppointment->id]) }}" class="btn btn-outline">                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />                        Voltar
                    </a>
                    <button type="submit" class="btn btn-danger">                        <x-icon name="trash-can-outline" class="w-4 h-4 mr-2" />                        Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
