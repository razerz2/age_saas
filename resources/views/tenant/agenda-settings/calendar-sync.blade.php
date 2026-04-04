@extends('layouts.tailadmin.app')

@section('title', 'Sincronização de Calendário')
@section('page', 'agenda-settings-sync')

@section('content')
@php
    $doctor = $calendar->doctor;
    $doctorUser = optional($doctor)->user;
    $doctorName = $doctorUser->display_name ?? $doctorUser->name ?? 'Profissional';
    $googleToken = optional($doctor)->googleCalendarToken;
    $appleToken = optional($doctor)->appleCalendarToken;
@endphp

<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sincronização de Calendário</h1>
        <nav class="mt-2 flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
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
                        <span class="ml-1 text-gray-500 dark:text-gray-400">Sincronização</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-xl border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Profissional</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Nome</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $doctorName }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Agenda</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $calendar->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Última sincronização</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $lastSyncAt ? \Carbon\Carbon::parse($lastSyncAt)->format('d/m/Y H:i') : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if (!$canInitiateAuth)
        <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
            @if ($user && $user->role === 'admin')
                A governança administrativa permite revogar vínculos, mas a autenticação da conta deve ser feita pelo próprio profissional.
            @elseif ($user && $user->role === 'user')
                Seu perfil não possui permissão para conectar contas de calendário.
            @endif
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Google Calendar</h3>
                    @if ($googleToken)
                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Conectado</span>
                    @else
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">Não conectado</span>
                    @endif
                </div>
            </div>
            <div class="space-y-4 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    OAuth da conta pessoal do profissional.
                </p>
                <div class="flex flex-wrap gap-2">
                    @if ($googleToken && $canRevokeConnection)
                        <form action="{{ workspace_route('tenant.integrations.google.disconnect', ['doctor' => $doctor->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="calendar_id" value="{{ $calendar->id }}">
                            <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                Desconectar
                            </button>
                        </form>
                    @endif

                    @if ($canInitiateAuth)
                        <a href="{{ workspace_route('tenant.integrations.google.connect', ['doctor' => $doctor->id, 'calendar_id' => $calendar->id]) }}" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">
                            {{ $googleToken ? 'Trocar conta' : 'Conectar Google Calendar' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Apple Calendar</h3>
                    @if ($appleToken)
                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Conectado</span>
                    @else
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">Não conectado</span>
                    @endif
                </div>
            </div>
            <div class="space-y-4 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Conexão por CalDAV com credenciais do profissional.
                </p>
                <div class="flex flex-wrap gap-2">
                    @if ($appleToken && $canRevokeConnection)
                        <form action="{{ workspace_route('tenant.integrations.apple.disconnect', ['doctor' => $doctor->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="calendar_id" value="{{ $calendar->id }}">
                            <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                Desconectar
                            </button>
                        </form>
                    @endif

                    @if ($canInitiateAuth)
                        <a href="{{ workspace_route('tenant.integrations.apple.connect.form', ['doctor' => $doctor->id, 'calendar_id' => $calendar->id]) }}" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">
                            {{ $appleToken ? 'Trocar conta' : 'Conectar Apple Calendar' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">
            <x-icon name="arrow-left" class="mr-2 h-4 w-4" />
            Voltar para Agenda do Profissional
        </a>
    </div>
</div>
@endsection
