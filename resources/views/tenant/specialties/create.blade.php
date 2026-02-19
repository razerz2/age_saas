@extends('layouts.tailadmin.app')

@section('title', 'Criar Especialidade')
@section('page', 'specialties')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.specialties.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Especialidades</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="specialties" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="medical-bag" size="text-xl" class="mr-2 text-blue-600" />
                        Nova Especialidade
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Preencha os dados abaixo para cadastrar uma nova especialidade médica</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form class="space-y-8" action="{{ workspace_route('tenant.specialties.store') }}" method="POST">
                @csrf

                <!-- Seção: Dados da Especialidade -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="information-outline" size="text-lg" class="mr-2 text-blue-600" />
                        Informações da Especialidade
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror" 
                                   name="name" value="{{ old('name') }}" 
                                   placeholder="Ex: Cardiologia, Pediatria, etc." required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Código
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('code') border-red-500 @enderror" 
                                   name="code" value="{{ old('code') }}" 
                                   maxlength="50" placeholder="Código CBO (opcional)">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Código CBO da especialidade</p>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                @if(tenant_setting('professional.customization_enabled'))
                    <!-- Seção: Personalização de Rótulos -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-icon name="tag-outline" size="text-lg" class="mr-2 text-blue-600" />
                            Personalização de Rótulos
                        </h3>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <x-icon name="information-outline" size="text-sm" class="mr-3 mt-0.5 text-blue-600 dark:text-blue-400" />
                                <div class="flex-1">
                                    <p class="text-blue-800 dark:text-blue-200 text-sm">Configure rótulos personalizados para esta especialidade. Estes valores sobrescrevem os rótulos globais.</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Rótulo Singular
                                </label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('label_singular') border-red-500 @enderror" 
                                       name="label_singular" value="{{ old('label_singular') }}" 
                                       placeholder="Ex: Psicólogo, Dentista"
                                       maxlength="50">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Exemplo: "Psicólogo" ou "Dentista"</p>
                                @error('label_singular')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Rótulo Plural
                                </label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('label_plural') border-red-500 @enderror" 
                                       name="label_plural" value="{{ old('label_plural') }}" 
                                       placeholder="Ex: Psicólogos, Dentistas"
                                       maxlength="50">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Exemplo: "Psicólogos" ou "Dentistas"</p>
                                @error('label_plural')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Rótulo de Registro
                                </label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('registration_label') border-red-500 @enderror" 
                                       name="registration_label" value="{{ old('registration_label') }}" 
                                       placeholder="Ex: CRP, CRO"
                                       maxlength="50">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Exemplo: "CRP" ou "CRO"</p>
                                @error('registration_label')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.specialties.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary inline-flex items-center">
                        <x-icon name="content-save-outline" size="text-sm" class="mr-2" />
                        Salvar Especialidade
                    </button>
                </div>
            </form>
        </div>
    </div>


@endsection


