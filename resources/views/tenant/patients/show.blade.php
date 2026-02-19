@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Paciente')
@section('page', 'patients')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <x-icon name="mdi-home-outline" size="text-base" class="mr-2" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-icon name="mdi-chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.patients.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            Pacientes
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-icon name="mdi-chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400">Detalhes</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalhes do Paciente</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize as informações do paciente</p>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if ($patient->is_active)
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                <x-icon name="mdi-check-circle-outline" size="text-sm" class="mr-2" />
                Ativo
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                <x-icon name="mdi-close-circle-outline" size="text-sm" class="mr-2" />
                Inativo
            </span>
        @endif
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <x-icon name="mdi-account-outline" size="text-lg" class="text-blue-600 mr-2" />
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações do Paciente</h2>
            </div>
        </div>
        
        <div class="p-6 space-y-8">
            <!-- Informações Pessoais -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <x-icon name="mdi-information-outline" size="text-lg" class="text-blue-600 mr-2" />
                    Informações Pessoais
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            ID
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ truncate_uuid($patient->id) }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Nome Completo
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->full_name }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            CPF
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->cpf ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Data de Nascimento
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : 'N/A' }}
                            @if($patient->birth_date)
                                <span class="text-gray-500 dark:text-gray-400 text-xs ml-2">
                                    ({{ $patient->birth_date->age }} anos)
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Gênero
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($patient->gender)
                                {{ $patient->gender->name }} ({{ $patient->gender->abbreviation }})
                            @else
                                <span class="text-gray-500 dark:text-gray-400">N/A</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informações de Contato -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <x-icon name="mdi-phone-outline" size="text-lg" class="text-blue-600 mr-2" />
                    Informações de Contato
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            E-mail
                        </label>
                        @if($patient->email)
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                <a href="mailto:{{ $patient->email }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $patient->email }}
                                </a>
                            </p>
                        @else
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">N/A</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Telefone
                        </label>
                        @if($patient->phone)
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                <a href="tel:{{ $patient->phone }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $patient->phone }}
                                </a>
                            </p>
                        @else
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">N/A</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informações de Endereço -->
            @if($patient->address)
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <x-icon name="mdi-map-marker-outline" size="text-lg" class="text-blue-600 mr-2" />
                    Endereço
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Logradouro
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->street ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Número
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->number ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Complemento
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->complement ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Bairro
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->neighborhood ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Cidade
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->city ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            Estado (UF)
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->state ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            CEP
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->address->postal_code ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informações Adicionais -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            <x-icon name="mdi-calendar-month-outline" size="text-sm" class="inline mr-1" />
                            Criado em
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            <x-icon name="mdi-sync" size="text-sm" class="inline mr-1" />
                            Atualizado em
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between flex-nowrap mt-6 border-t pt-4">
                <a href="{{ workspace_route('tenant.patients.index') }}"
                   class="btn btn-outline inline-flex items-center">
                    <x-icon name="mdi-arrow-left" size="text-sm" class="mr-2" />
                    Voltar
                </a>
                <div class="flex items-center gap-3">
                    @can('patients.update')
                        <a href="{{ workspace_route('tenant.patients.edit', $patient->id) }}"
                           class="btn btn-primary inline-flex items-center">
                            <x-icon name="mdi-pencil-outline" size="text-sm" class="mr-2" />
                            Editar
                        </a>
                    @endcan
                    <form action="{{ workspace_route('tenant.patients.destroy', $patient->id) }}" method="POST" class="inline"
                          data-confirm-submit="true"
                          data-confirm-title="Excluir paciente"
                          data-confirm-message="Tem certeza que deseja excluir este paciente? Esta ação não pode ser desfeita."
                          data-confirm-confirm-text="Excluir"
                          data-confirm-cancel-text="Cancelar"
                          data-confirm-type="error">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger inline-flex items-center">
                            <x-icon name="mdi-trash-can-outline" size="text-sm" class="mr-2" />
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
