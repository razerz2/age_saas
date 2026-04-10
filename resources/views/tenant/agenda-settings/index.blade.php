@extends('layouts.tailadmin.app')

@section('title', 'Agenda do Profissional')
@section('page', 'agenda-settings-index')

@section('content')
@php
    $gridRows = $calendars->getCollection()->map(function ($calendar) {
        $doctorName = optional(optional($calendar->doctor)->user)->display_name
            ?? optional(optional($calendar->doctor)->user)->name
            ?? 'Profissional';

        return [
            'professional' => $doctorName,
            'agenda' => $calendar->name,
            'external_id' => $calendar->external_id ?: '-',
            'status_badge' => $calendar->is_active
                ? '<span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Ativa</span>'
                : '<span class="inline-flex rounded-full bg-gray-200 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">Inativa</span>',
            'business_hours_count' => (int) ($calendar->business_hours_count ?? 0),
            'appointment_types_summary' => (int) ($calendar->appointment_types_active_count ?? 0) . ' ativos / ' . (int) ($calendar->appointment_types_count ?? 0) . ' total',
            'actions' => view('tenant.agenda-settings.partials.actions', compact('calendar'))->render(),
        ];
    })->values()->all();
@endphp

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Agenda do Profissional</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                <x-icon name="home-outline" size="text-base" class="mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Agenda do Profissional</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <x-help-button module="agenda-settings" />
                <a href="{{ workspace_route('tenant.agenda-settings.create') }}" class="btn btn-primary inline-flex items-center">
                    <x-icon name="plus" size="text-sm" class="mr-2" />
                    Nova Agenda
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
            <div class="flex">
                <x-icon name="check-circle-outline" size="text-lg" class="text-green-600 dark:text-green-400" />
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Agendas</h2>
        </div>

        <div class="p-6">
            <div class="mb-4">
                <form method="GET" action="{{ workspace_route('tenant.agenda-settings.index') }}" class="flex items-center space-x-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Buscar por agenda, identificador externo ou profissional"
                        class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                    <button type="submit" class="btn btn-outline">Buscar</button>
                    @if ($search !== '')
                        <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="btn btn-outline inline-flex items-center">
                            <x-icon name="filter-remove-outline" class="w-4 h-4 mr-2" />
                            Limpar
                        </a>
                    @endif
                </form>
            </div>

            <div
                id="agenda-settings-grid-wrapper"
                data-row-click-link-selector='a[title="Visualizar"]'
            >
                <x-tenant.grid
                    id="agenda-settings-grid"
                    :columns="[
                        ['name' => 'professional', 'label' => 'Profissional'],
                        ['name' => 'agenda', 'label' => 'Agenda'],
                        ['name' => 'external_id', 'label' => 'ID Externo'],
                        ['name' => 'status_badge', 'label' => 'Status'],
                        ['name' => 'business_hours_count', 'label' => 'Horários'],
                        ['name' => 'appointment_types_summary', 'label' => 'Tipos'],
                        ['name' => 'actions', 'label' => 'Ações'],
                    ]"
                    :data="$gridRows"
                    :pagination="false"
                    :search="false"
                    :sort="true"
                />
            </div>

            @if ($calendars->hasPages())
                <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                    {{ $calendars->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
