@extends('layouts.tailadmin.app')

@section('title', 'Criar Tipo de Consulta')
@section('page', 'appointment-types')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white inline-flex items-center gap-2">
                            <x-icon name="home-outline" class="w-5 h-5" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Tipos de Consulta</a>
                    </li>
                    <li class="flex items-center gap-2 text-gray-900 dark:text-white font-semibold">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <span>Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="appointment-types" />
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-4xl">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Novo Tipo de Consulta</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Preencha os dados abaixo para cadastrar um novo tipo de consulta</p>
            </div>

            <form class="space-y-8" action="{{ workspace_route('tenant.appointment-types.store') }}" method="POST">
                @csrf

                <!-- Seção: Informações do Tipo de Consulta -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informações do Tipo de Consulta</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico...</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->display_name ?? $doctor->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                                   name="name" value="{{ old('name') }}"
                                   placeholder="Ex: Consulta Médica, Retorno, etc." required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Duração (minutos) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('duration_min') border-red-500 @enderror"
                                   name="duration_min" value="{{ old('duration_min', 30) }}"
                                   min="1" placeholder="30" required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tempo de duração da consulta</p>
                            @error('duration_min')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status
                            </label>
                            <select name="is_active" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('is_active') border-red-500 @enderror">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-4 h-4 mr-2" />
                        Salvar Tipo de Consulta
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
