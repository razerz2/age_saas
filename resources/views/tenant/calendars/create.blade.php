@extends('layouts.tailadmin.app')

@section('title', 'Criar Calendário')
@section('page', 'calendars')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" class="w-5 h-5" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <a href="{{ workspace_route('tenant.calendars.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Calendários</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="calendars" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="calendar-outline" class="w-6 h-6 mr-2 text-blue-600" />
                        Novo Calendário
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Preencha os dados abaixo para criar um novo Calendário</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form class="space-y-8" action="{{ workspace_route('tenant.calendars.store') }}" method="POST">
                @csrf

                <!-- Seção: Informações do Calendário -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Informações do Calendário
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="account-outline" class="w-4 h-4 inline mr-1" />
                                médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="form-textbox" class="w-4 h-4 inline mr-1" />
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror" 
                                   name="name" value="{{ old('name') }}" 
                                   placeholder="Ex: Calendário Principal" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="identifier" class="w-4 h-4 inline mr-1" />
                                ID Externo
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('external_id') border-red-500 @enderror" 
                                   name="external_id" value="{{ old('external_id') }}" 
                                   placeholder="ID do Calendário em sistema externo (opcional)">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ID usado para sincronização com Calendários externos</p>
                            @error('external_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.calendars.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-4 h-4 mr-2" />
                        Salvar Calendário
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
