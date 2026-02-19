@extends('layouts.tailadmin.app')

@section('title', 'Editar Médico')
@section('page', 'doctors')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Editar Médico</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                <x-icon name="home-outline" size="text-base" class="mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.doctors.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white md:ml-2">Médicos</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
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
                        <x-icon name="pencil-outline" size="text-xl" class="mr-2 text-blue-600" />
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
                        <x-icon name="information-outline" size="text-lg" class="mr-2 text-blue-600" />
                        Informações Básicas
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <x-icon name="account-outline" size="text-sm" class="inline mr-1" />
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
                        <x-icon name="briefcase-outline" size="text-lg" class="mr-2 text-blue-600" />
                        Dados Profissionais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="card-account-details-outline" size="text-sm" class="inline mr-1" />
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
                                <x-icon name="map-marker-outline" size="text-sm" class="inline mr-1" />
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
                            <x-icon name="pencil-outline" size="text-sm" class="inline mr-1" />
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
                            <x-icon name="tune-variant" size="text-lg" class="mr-2 text-blue-600" />
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
                        <x-icon name="stethoscope" size="text-lg" class="mr-2 text-blue-600" />
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
                                    <button type="button" id="add-specialty-btn" class="btn btn-primary flex-1">
                                        <x-icon name="plus" size="text-sm" class="mr-2" />
                                        Adicionar
                                    </button>
                                    <button type="button" id="clear-specialties-btn" class="btn btn-outline">
                                        <x-icon name="trash-can-outline" size="text-sm" class="mr-2" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Área para exibir especialidades selecionadas -->
                        @php
                            $doctorSpecialties = $doctor->specialties->pluck('id')->toArray();
                            $selectedIds = old('specialties', $doctorSpecialties);
                        @endphp
                        <div id="selected-specialties" class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700" style="min-height: 60px;" data-badge-style="bootstrap" data-initial-selected='@json($selectedIds)'>
                            @if(!empty($selectedIds))
                                @foreach($selectedIds as $specialtyId)
                                    @php
                                        $specialty = $specialties->firstWhere('id', $specialtyId);
                                    @endphp
                                    @if($specialty)
                                        <span class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm font-medium mr-2 mb-2 specialty-badge" data-id="{{ $specialty->id }}">
                                            <x-icon name="file-document-outline" size="text-sm" />
                                            {{ $specialty->name }}
                                            <button type="button" class="btn-close ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100" aria-label="Remover">&times;</button>
                                        </span>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-gray-500 dark:text-gray-400 mb-0">
                                <x-icon name="information-outline" size="text-sm" class="inline mr-1" />
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
                    <a href="{{ workspace_route('tenant.doctors.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary inline-flex items-center">
                        <x-icon name="content-save-outline" size="text-sm" class="mr-2" />
                        Atualizar Médico
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
