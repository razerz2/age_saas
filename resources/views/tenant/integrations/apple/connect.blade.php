@extends('layouts.tailadmin.app')

@section('title', 'Conectar Apple Calendar')
@section('page', 'integrations')

@section('content')

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.integrations.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Integrações</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.integrations.apple.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Apple Calendar</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Conectar</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

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

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        @foreach ($errors->all() as $error)
                            {{ $error }}{{ !$loop->last ? ' | ' : '' }}
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 pt-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <x-icon name="apple" class="text-blue-600" />
                Conectar Apple Calendar
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Configure as credenciais do iCloud para {{ $doctor->user->name_full ?? $doctor->user->name }}.
            </p>
        </div>
            <div class="p-6">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/30">
                    <div class="flex items-center gap-2">
                        <x-icon name="information-outline" size="text-lg" class="text-gray-600 dark:text-gray-300" />
                        <h4 class="font-semibold text-gray-800 dark:text-white">Como obter suas credenciais do iCloud</h4>
                    </div>
                    <ol class="mt-3 list-decimal pl-5 space-y-2 text-sm leading-6 text-gray-700 dark:text-gray-300">
                        <li>
                            Acesse <a href="https://appleid.apple.com/account/manage" target="_blank" rel="noopener noreferrer" class="font-medium text-brand-600 hover:underline dark:text-brand-400">appleid.apple.com</a> e faça login com sua conta Apple.
                        </li>
                        <li>
                            Na seção "Segurança", encontre "Senhas de app" e clique em "Gerar senha de app".
                        </li>
                        <li>
                            Dê um nome para a senha (ex: "Agendamento SaaS") e clique em "Criar".
                        </li>
                        <li>
                            Copie a senha gerada (ela só será exibida uma vez) e use no formulário abaixo.
                        </li>
                        <li>
                            Use seu email do iCloud como usuário e a senha de app gerada como senha.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="apple" size="text-lg" class="mr-2 text-blue-600 dark:text-blue-400" />
                    Credenciais
                </h2>
            </div>
            <div class="p-6">
                <form action="{{ workspace_route('tenant.integrations.apple.connect', ['doctor' => $doctor->id]) }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email do iCloud <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="username"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="seu.email@icloud.com"
                                required
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('username') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">O email da sua conta Apple/iCloud.</p>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Senha de App <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="xxxx-xxxx-xxxx-xxxx"
                                required
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('password') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Senha de app gerada no Apple ID. Não use a senha da conta.</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="server_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                URL do servidor CalDAV
                            </label>
                            <input
                                type="url"
                                id="server_url"
                                name="server_url"
                                value="{{ old('server_url', 'https://caldav.icloud.com') }}"
                                placeholder="https://caldav.icloud.com"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('server_url') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Para iCloud, use https://caldav.icloud.com.</p>
                            @error('server_url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="calendar_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                URL do calendário (opcional)
                            </label>
                            <input
                                type="text"
                                id="calendar_url"
                                name="calendar_url"
                                value="{{ old('calendar_url') }}"
                                placeholder="/calendars/seu-email@icloud.com/"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('calendar_url') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Deixe em branco para descoberta automática do calendário.</p>
                            @error('calendar_url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ workspace_route('tenant.integrations.apple.index') }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="text-sm" />
                            Conectar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
