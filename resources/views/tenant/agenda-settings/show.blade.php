@extends('layouts.tailadmin.app')

@section('title', 'Visualizar Agenda do Profissional')
@section('page', 'agenda-settings-show')

@section('content')
@php
    $doctorName = optional($calendar->doctor->user)->display_name ?? optional($calendar->doctor->user)->name ?? 'Profissional';
    $weekdays = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];
@endphp

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Visualizar Agenda do Profissional</h1>
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
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Visualizar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Profissional: {{ $doctorName }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="btn btn-outline inline-flex items-center">
                    <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                    Voltar
                </a>
                <a href="{{ workspace_route('tenant.agenda-settings.edit', $calendar->id) }}" class="btn btn-primary inline-flex items-center">
                    <x-icon name="pencil-outline" class="w-4 h-4 mr-2" />
                    Editar
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

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dados principais</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Profissional</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $doctorName }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Agenda</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $calendar->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">ID externo</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $calendar->external_id ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $calendar->is_active ? 'Ativa' : 'Inativa' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Horários de atendimento</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Dia</th>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Início</th>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Fim</th>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Intervalo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($businessHours as $hour)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $weekdays[$hour->weekday] ?? $hour->weekday }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">{{ substr((string) $hour->start_time, 0, 5) }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">{{ substr((string) $hour->end_time, 0, 5) }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        @if ($hour->break_start_time && $hour->break_end_time)
                                            {{ substr((string) $hour->break_start_time, 0, 5) }} - {{ substr((string) $hour->break_end_time, 0, 5) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-3 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum horário cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tipos vinculados</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Nome</th>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Duração</th>
                                <th class="px-3 py-2 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($appointmentTypes as $type)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $type->name }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $type->duration_min }} min</td>
                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $type->is_active ? 'Ativo' : 'Inativo' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-3 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum tipo cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
