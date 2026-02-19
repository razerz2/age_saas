@extends('layouts.tailadmin.app')

@section('title', 'Editar Tipo de Consulta')
@section('page', 'appointment-types')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Tipo de Consulta</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Tipos de Consulta</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <x-help-button module="appointment-types" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="clipboard-text-outline" class="w-6 h-6 mr-2 text-blue-600" />
                        Editar Tipo de Consulta
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Atualize as informações do tipo de consulta abaixo</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form class="space-y-8" action="{{ workspace_route('tenant.appointment-types.update', $appointmentType->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Seção: Informações do Tipo de Consulta -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Informações do Tipo de Consulta
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="account-outline" class="w-4 h-4 inline mr-1" />
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico...</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointmentType->doctor_id) == $doctor->id ? 'selected' : '' }}>
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
                                <x-icon name="form-textbox" class="w-4 h-4 inline mr-1" />
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror" 
                                   name="name" 
                                   value="{{ old('name', $appointmentType->name) }}" 
                                   placeholder="Ex: Consulta Médica, Retorno, etc." 
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Configurações Adicionais -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="cog-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Configurações Adicionais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="clock-outline" class="w-4 h-4 inline mr-1" />
                                Duração (minutos) <span class="text-gray-500 dark:text-gray-400 text-xs">(opcional)</span>
                            </label>
                            <input type="number" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('duration_min') border-red-500 @enderror" 
                                   name="duration_min" 
                                   value="{{ old('duration_min', $appointmentType->duration_min) }}" 
                                   min="1" 
                                   placeholder="Tempo padrão em minutos">
                            <small class="text-gray-500 dark:text-gray-400">Tempo padrão para consultas deste tipo</small>
                            @error('duration_min')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <x-icon name="toggle-switch-outline" class="w-4 h-4 mr-1" />
                                    Status
                                </label>
                                <select name="is_active" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('is_active') border-red-500 @enderror">
                                    <option value="1" {{ old('is_active', $appointmentType->is_active) == 1 ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('is_active', $appointmentType->is_active) == 0 ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-4 h-4 mr-2" />
                        Atualizar Tipo de Consulta
                    </button>
                </div>
            </form>
        </div>
    </div>


@endsection
