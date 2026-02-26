@extends('layouts.tailadmin.app')

@section('title', 'Agendamentos')
@section('page', 'appointments')

@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Agendamentos</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 inline-flex items-center">
                                <x-icon name="home-outline" class="w-4 h-4 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                                <span class="ml-1 text-gray-500">Agendamentos</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ workspace_route('tenant.appointments.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Novo Agendamento
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-icon name="alert-outline" class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Não é possível criar agendamentos</h3>
                    <div class="mt-2 text-sm text-red-700">{!! session('error') !!}</div>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-icon name="check-bold" class="h-5 w-5 text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{!! session('success') !!}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Agendamentos</h2>
        </div>

        <div class="p-6">
            <div
                id="appointments-grid-wrapper"
                data-show-url-template="{{ workspace_route('tenant.appointments.show', '__APPOINTMENT_ID__') }}"
                data-row-click-link-selector='a[title="Ver"]'
            >
            <x-tenant.grid
                id="appointments-grid"
                :columns="[
                    ['name' => 'date', 'label' => 'Data'],
                    ['name' => 'time', 'label' => 'Hora'],
                    ['name' => 'patient', 'label' => 'Paciente'],
                    ['name' => 'doctor', 'label' => 'Médico'],
                    ['name' => 'specialty', 'label' => 'Especialidade'],
                    ['name' => 'mode', 'label' => 'Modo'],
                    ['name' => 'status_badge', 'label' => 'Status'],
                    ['name' => 'actions', 'label' => 'Ações'],
                ]"
                ajaxUrl="{{ workspace_route('tenant.appointments.grid-data') }}"
                :pagination="true"
                :search="true"
                :sort="true"
            />
            </div>
        </div>
    </div>

@endsection
