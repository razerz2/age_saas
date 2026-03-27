@extends('layouts.tailadmin.app')

@section('title', 'Editar Agenda do Profissional')
@section('page', 'agenda-settings-form')

@section('content')
@php
    $doctorName = optional($calendar->doctor->user)->display_name ?? optional($calendar->doctor->user)->name ?? 'Profissional';

    $businessHoursRows = $calendar->doctor->businessHours
        ->sortBy(fn ($hour) => sprintf('%d-%s', (int) $hour->weekday, (string) $hour->start_time))
        ->map(fn ($hour) => [
            'weekday' => (int) $hour->weekday,
            'start_time' => substr((string) $hour->start_time, 0, 5),
            'end_time' => substr((string) $hour->end_time, 0, 5),
            'break_start_time' => $hour->break_start_time ? substr((string) $hour->break_start_time, 0, 5) : '',
            'break_end_time' => $hour->break_end_time ? substr((string) $hour->break_end_time, 0, 5) : '',
        ])->values()->all();

    $appointmentTypesRows = $calendar->doctor->appointmentTypes
        ->sortByDesc('is_active')
        ->map(fn ($type) => [
            'id' => $type->id,
            'name' => $type->name,
            'duration_min' => $type->duration_min,
            'is_active' => $type->is_active ? '1' : '0',
        ])->values()->all();
@endphp

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Agenda do Profissional</h1>
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
                                <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Agenda do Profissional</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Profissional: {{ $doctorName }}</p>
            </div>
            <a href="{{ workspace_route('tenant.agenda-settings.show', $calendar->id) }}" class="btn btn-outline inline-flex items-center">
                <x-icon name="eye-outline" class="w-4 h-4 mr-2" />
                Visualizar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                <div class="ml-3">
                    <ul class="list-disc pl-5 text-sm text-red-800 dark:text-red-200">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edição da Agenda</h2>
        </div>

        <div class="p-6">
            @include('tenant.agenda-settings.partials.form', [
                'formAction' => workspace_route('tenant.agenda-settings.update', $calendar->id),
                'formMethod' => 'PUT',
                'submitLabel' => 'Atualizar Agenda',
                'isEdit' => true,
                'calendar' => $calendar,
                'doctors' => collect(),
                'businessHoursRows' => $businessHoursRows,
                'appointmentTypesRows' => $appointmentTypesRows,
            ])
        </div>
    </div>
</div>
@endsection
