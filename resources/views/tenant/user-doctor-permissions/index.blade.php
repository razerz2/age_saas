@extends('layouts.tailadmin.app')

@section('title', 'Gerenciar Permissões de Médicos')
@section('page', 'user-doctor-permissions')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-icon name="shield-account-outline" size="text-xl" class="text-blue-600 dark:text-blue-400" />
                        Gerenciar Permissões de Médicos
                    </h1>
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                    class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    <x-icon name="home-outline" size="text-base" />
                                    Dashboard
                                </a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.users.index') }}"
                                    class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Usuários</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.users.show', $user->id) }}"
                                    class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ $user->name }}</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="text-gray-900 dark:text-white font-semibold">Permissões de Médicos</span>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="flex-shrink-0">
                    <x-help-button module="users" />
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Permissões de Médicos — {{ $user->name_full }}
                </h2>
            </div>

            <form id="doctor-permissions-form" action="{{ workspace_route('tenant.users.doctor-permissions.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-900 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-100">
                        <div class="flex gap-3">
                            <x-icon name="information-outline" size="text-lg" class="mt-0.5 text-blue-700 dark:text-blue-300" />
                            <div class="text-sm">
                                <p>Selecione os médicos que este usuário pode visualizar nas agendas.</p>
                                <p class="mt-1">
                                    <strong>Nota:</strong> se nenhum médico for selecionado, o usuário poderá visualizar todos os médicos.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($doctors->isEmpty())
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-100">
                            <div class="flex items-center gap-2 text-sm">
                                <x-icon name="information-outline" size="text-lg" class="text-gray-600 dark:text-gray-300" />
                                Nenhum médico cadastrado no sistema.
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($doctors as $doctor)
                                <label for="doctor_{{ $doctor->id }}"
                                    class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40 cursor-pointer">
                                    <input class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        type="checkbox" name="doctor_ids[]" value="{{ $doctor->id }}"
                                        id="doctor_{{ $doctor->id }}"
                                        {{ in_array($doctor->id, $userPermissions) ? 'checked' : '' }}>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $doctor->user->name_full ?? 'Sem nome' }}
                                        </div>
                                        @if ($doctor->crm_number)
                                            <div class="text-xs text-gray-500 dark:text-gray-300 mt-1">
                                                CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }}
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <!-- Barra de Ações -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ workspace_route('tenant.users.show', $user->id) }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="content-save-outline" size="text-sm" />
                            Salvar Permissões
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
