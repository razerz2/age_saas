@extends('layouts.tailadmin.app')

@section('title', 'Editar Médico')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Médico</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.doctors.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Médicos</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <x-help-button module="doctors" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Médico
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Atualize as informações do médico abaixo</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <form class="space-y-8" action="{{ workspace_route('tenant.doctors.update', $doctor->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Seção: Informações Básicas -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informações Básicas
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7 7z"></path>
                            </svg>
                            Usuário <span class="text-red-500">*</span>
                        </label>
                        <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('user_id') border-red-500 @enderror" required>
                            <option value="">Selecione um usuário</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $doctor->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Dados Profissionais -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A6.002 6.002 0 0015 7.001V5a4 4 0 00-8 0v2.001A6.002 6.002 0 001 13.255V16a2 2 0 002 2h16a2 2 0 002-2v-2.745zM9 5a2 2 0 014 0v2.001H9V5z"></path>
                        </svg>
                        Dados Profissionais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                </svg>
                                Número CRM, CRP ou CRO
                            </label>
                            <input type="text"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('crm_number') border-red-500 @enderror"
                                   name="crm_number"
                                   value="{{ old('crm_number', $doctor->crm_number) }}"
                                   maxlength="50"
                                   placeholder="Ex: 123456">
                            @error('crm_number')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Estado CRM, CRP ou CRO
                            </label>
                            <input type="text"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('crm_state') border-red-500 @enderror"
                                   name="crm_state"
                                   value="{{ old('crm_state', $doctor->crm_state) }}"
                                   maxlength="2"
                                   placeholder="Ex: SP">
                            <small class="text-gray-500 dark:text-gray-400">Digite a sigla do estado (2 letras)</small>
                            @error('crm_state')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            Assinatura
                        </label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('signature') border-red-500 @enderror"
                                  name="signature"
                                  rows="4"
                                  placeholder="Digite a assinatura do médico (opcional)">{{ old('signature', $doctor->signature) }}</textarea>
                        @error('signature')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @php
                    $customizationEnabled = tenant_setting('professional.customization_enabled') === 'true';
                @endphp

                @if($customizationEnabled)
                    <!-- Seção: Personalização do Profissional -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Personalização do Profissional (Opcional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tipo do Profissional (Singular)
                                </label>
                                <input type="text"
                                       name="label_singular"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('label_singular') border-red-500 @enderror"
                                       placeholder="Ex: Psicólogo, Fisioterapeuta"
                                       value="{{ old('label_singular', $doctor->label_singular ?? '') }}"
                                       maxlength="60">
                                @error('label_singular')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tipo do Profissional (Plural)
                                </label>
                                <input type="text"
                                       name="label_plural"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('label_plural') border-red-500 @enderror"
                                       placeholder="Ex: Psicólogos, Fisioterapeutas"
                                       value="{{ old('label_plural', $doctor->label_plural ?? '') }}"
                                       maxlength="60">
                                @error('label_plural')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Registro Profissional (Rótulo)
                                </label>
                                <input type="text"
                                       name="registration_label"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('registration_label') border-red-500 @enderror"
                                       placeholder="Ex: CRM, CRP, CRO, CREFITO"
                                       value="{{ old('registration_label', $doctor->registration_label ?? '') }}"
                                       maxlength="40">
                                @error('registration_label')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Registro Profissional Completo (Valor)
                                </label>
                                <input type="text"
                                       name="registration_value"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('registration_value') border-red-500 @enderror"
                                       placeholder="Ex: CRM 55221, CRP 05/19999, CREFITO 123456-F"
                                       value="{{ old('registration_value', $doctor->registration_value ?? '') }}"
                                       maxlength="100">
                                @error('registration_value')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Seção: Especialidades Médicas -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Especialidades Médicas
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selecione as especialidades do médico <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                            <div class="md:col-span-8">
                                <select id="specialty-select" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('specialties') border-red-500 @enderror">
                                    <option value="">Selecione uma especialidade</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" data-name="{{ $specialty->name }}">{{ $specialty->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-4">
                                <div class="flex gap-2">
                                    <button type="button" id="add-specialty-btn" class="flex-1 px-4 py-2 bg-primary text-white hover:bg-primary/90 font-medium rounded-md transition-colors">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Adicionar
                                    </button>
                                    <button type="button" id="clear-specialties-btn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium rounded-md transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Área para exibir especialidades selecionadas -->
                        <div id="selected-specialties" class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700" style="min-height: 60px;">
                            @php
                                $doctorSpecialties = $doctor->specialties->pluck('id')->toArray();
                                $selectedIds = old('specialties', $doctorSpecialties);
                            @endphp
                            @if(!empty($selectedIds))
                                @foreach($selectedIds as $specialtyId)
                                    @php
                                        $specialty = $specialties->firstWhere('id', $specialtyId);
                                    @endphp
                                    @if($specialty)
                                        <span class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm font-medium mr-2 mb-2 specialty-badge" data-id="{{ $specialty->id }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ $specialty->name }}
                                            <button type="button" class="btn-close ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100" aria-label="Remover">&times;</button>
                                        </span>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-gray-500 dark:text-gray-400 mb-0">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Nenhuma especialidade selecionada
                                </p>
                            @endif
                        </div>

                        <!-- Campos hidden para enviar os IDs (serão criados dinamicamente pelo JavaScript) -->
                        <div id="specialties-inputs"></div>

                        @error('specialties')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.doctors.index') }}" class="btn-patient-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7v10a2 2 0 002-2h4v-4a2 2 0 00-2-2h-4v-4a2 2 0 00-2-2z"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002 2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Atualizar Médico
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-doctors.css') }}" rel="stylesheet">
    <style>
        .btn-patient-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: #2563eb;
            color: white;
        }
        
        .btn-patient-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .btn-patient-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: transparent;
            color: #374151;
            text-decoration: none;
        }
        
        .btn-patient-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }
        
        /* Dark mode styles */
        @media (prefers-color-scheme: dark) {
            .btn-patient-primary {
                background-color: transparent;
                border-color: #d1d5db;
                color: white;
            }
            
            .btn-patient-primary:hover {
                background-color: #1f2937;
                border-color: #9ca3af;
            }
            
            .btn-patient-secondary {
                background-color: transparent;
                border-color: #d1d5db;
                color: white;
            }
            
            .btn-patient-secondary:hover {
                background-color: #1f2937;
                border-color: #9ca3af;
            }
        }
        
        /* For TailAdmin dark mode class */
        .dark .btn-patient-primary {
            background-color: transparent;
            border-color: #d1d5db;
            color: white;
        }
        
        .dark .btn-patient-primary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
        }
        
        .dark .btn-patient-secondary {
            background-color: transparent;
            border-color: #d1d5db;
            color: white;
        }
        
        .dark .btn-patient-secondary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
        }
    </style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let selectedSpecialties = [];
        
        // Carregar especialidades já selecionadas (do old ou do banco)
        function loadSelectedSpecialties() {
            @php
                $doctorSpecialties = $doctor->specialties->pluck('id')->toArray();
                $selectedIds = old('specialties', $doctorSpecialties);
            @endphp
            @if(!empty($selectedIds))
                selectedSpecialties = @json($selectedIds);
            @endif
            updateSpecialtiesDisplay();
        }
        
        // Atualizar exibição das especialidades selecionadas
        function updateSpecialtiesDisplay() {
            const container = $('#selected-specialties');
            container.empty();
            
            if (selectedSpecialties.length === 0) {
                container.html('<p class="text-muted mb-0"><i class="mdi mdi-information-outline me-1"></i>Nenhuma especialidade selecionada</p>');
                return;
            }
            
            selectedSpecialties.forEach(function(specialtyId) {
                const option = $('#specialty-select option[value="' + specialtyId + '"]');
                if (option.length) {
                    const name = option.data('name');
                    const badge = $('<span>')
                        .addClass('badge bg-primary me-2 mb-2 specialty-badge')
                        .attr('data-id', specialtyId)
                        .css({
                            'font-size': '13px', 
                            'padding': '8px 14px', 
                            'display': 'inline-flex', 
                            'align-items': 'center', 
                            'gap': '6px'
                        })
                        .html('<i class="mdi mdi-stethoscope"></i>' + name + '<button type="button" class="btn-close btn-close-white ms-1" style="font-size: 10px; opacity: 0.8;" aria-label="Remover"></button>');
                    container.append(badge);
                }
            });
            
            // Atualizar campos hidden
            const inputsContainer = $('#specialties-inputs');
            inputsContainer.empty();
            selectedSpecialties.forEach(function(specialtyId) {
                inputsContainer.append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'specialties[]')
                    .val(specialtyId)
                );
            });
        }
        
        // Adicionar especialidade
        $('#add-specialty-btn').on('click', function() {
            const select = $('#specialty-select');
            const specialtyId = select.val();
            
            if (!specialtyId) {
                showAlert({ type: 'warning', title: 'Atenção', message: 'Por favor, selecione uma especialidade' });
                return;
            }
            
            // Verificar se já foi adicionada
            if (selectedSpecialties.includes(specialtyId)) {
                showAlert({ type: 'warning', title: 'Atenção', message: 'Esta especialidade já foi adicionada' });
                return;
            }
            
            selectedSpecialties.push(specialtyId);
            updateSpecialtiesDisplay();
            select.val(''); // Limpar seleção
        });
        
        // Remover especialidade (delegation para elementos dinâmicos)
        $(document).on('click', '.specialty-badge .btn-close', function(e) {
            e.preventDefault();
            const badge = $(this).closest('.specialty-badge');
            const specialtyId = badge.data('id');
            
            selectedSpecialties = selectedSpecialties.filter(function(id) {
                return id !== specialtyId;
            });
            
            updateSpecialtiesDisplay();
        });
        
        // Limpar todas as especialidades
        $('#clear-specialties-btn').on('click', function() {
            if (selectedSpecialties.length === 0) {
                return;
            }
            
            confirmAction({
                title: 'Remover especialidades',
                message: 'Deseja remover todas as especialidades selecionadas?',
                confirmText: 'Remover',
                cancelText: 'Cancelar',
                type: 'warning',
                onConfirm: () => {
                    selectedSpecialties = [];
                    updateSpecialtiesDisplay();
                }
            });
        });
        
        // Permitir adicionar com Enter no select
        $('#specialty-select').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#add-specialty-btn').click();
            }
        });
        
        // Validação antes de enviar o formulário
        $('form').on('submit', function(e) {
            if (selectedSpecialties.length === 0) {
                e.preventDefault();
                showAlert({ type: 'warning', title: 'Atenção', message: 'Por favor, selecione pelo menos uma especialidade médica.' });
                $('#specialty-select').focus();
                return false;
            }
        });
        
        // Inicializar
        loadSelectedSpecialties();
    });
</script>
@endpush

@endsection
