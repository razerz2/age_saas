@extends('layouts.tailadmin.app')

@section('title', 'Sincronização Apple Calendar')
@section('page', 'agenda-settings-sync')

@section('content')
    @php
        $syncBackUrl = isset($returnCalendarId) && $returnCalendarId
            ? workspace_route('tenant.agenda-settings.calendar-sync', $returnCalendarId)
            : workspace_route('tenant.agenda-settings.index');
    @endphp
    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <nav aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li><a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-gray-800 dark:hover:text-white">Dashboard</a></li>
                    <li>/</li>
                    <li><a href="{{ workspace_route('tenant.agenda-settings.index') }}" class="hover:text-gray-800 dark:hover:text-white">Agenda do Profissional</a></li>
                    <li>/</li>
                    <li><a href="{{ $syncBackUrl }}" class="hover:text-gray-800 dark:hover:text-white">Sincronização</a></li>
                    <li>/</li>
                    <li class="font-medium text-gray-900 dark:text-white">Apple Calendar</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Conectar Apple Calendar</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Profissional: {{ $doctor->user->name_full ?? $doctor->user->name }}</p>
        </div>

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                {{ implode(' | ', $errors->all()) }}
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Orientações</h2>
            </div>
            <div class="p-6 text-sm text-gray-600 dark:text-gray-300">
                <ol class="list-decimal space-y-2 pl-5">
                    <li>Acesse <a href="https://appleid.apple.com/account/manage" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:underline dark:text-brand-400">appleid.apple.com</a>.</li>
                    <li>Gere uma senha de app na seção de segurança.</li>
                    <li>Use o email iCloud e a senha de app no formulario abaixo.</li>
                    <li>Se não informar <code>calendar_url</code>, o sistema tenta descobrir automaticamente.</li>
                </ol>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Credenciais CalDAV</h2>
            </div>

            <form action="{{ workspace_route('tenant.integrations.apple.connect', ['doctor' => $doctor->id]) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @if (isset($returnCalendarId) && $returnCalendarId)
                    <input type="hidden" name="calendar_id" value="{{ $returnCalendarId }}">
                @endif

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email iCloud <span class="text-red-500">*</span></label>
                        <input id="username" name="username" type="email" required value="{{ old('username') }}" placeholder="usuario@icloud.com"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Senha de app <span class="text-red-500">*</span></label>
                        <input id="password" name="password" type="password" required placeholder="xxxx-xxxx-xxxx-xxxx"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="server_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL do servidor</label>
                        <input id="server_url" name="server_url" type="url" value="{{ old('server_url', 'https://caldav.icloud.com') }}" placeholder="https://caldav.icloud.com"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="calendar_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL do calendário (opcional)</label>
                        <input id="calendar_url" name="calendar_url" type="text" value="{{ old('calendar_url') }}" placeholder="/calendars/.../"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <a href="{{ $syncBackUrl }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700/60">Cancelar</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Conectar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
