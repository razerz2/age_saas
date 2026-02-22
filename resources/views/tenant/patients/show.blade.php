@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Paciente')
@section('page', 'patients')

@section('content')

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
                        <a href="{{ workspace_route('tenant.patients.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Pacientes</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Visualizar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="patients" />
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">Detalhes do Paciente</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Visualize os dados do paciente abaixo</p>
            </div>

            <div class="p-6 space-y-8">
                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dados Pessoais</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nome Completo
                            </label>
                            <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->full_name }}</div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                CPF
                            </label>
                            <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->cpf ?: 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Data de Nascimento
                            </label>
                            <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Gênero
                            </label>
                            <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @if($patient->gender)
                                    {{ $patient->gender->name }} ({{ $patient->gender->abbreviation }})
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações de Contato</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                E-mail
                            </label>
                            <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm break-all dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->email ?: 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Telefone
                            </label>
                            <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->phone ?: 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Endereço</h5>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4" data-patient-address-grid="line-1">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Logradouro
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->street ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Número
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->number ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Complemento
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->complement ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Bairro
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->neighborhood ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4" data-patient-address-grid="line-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    CEP
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->postal_code ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Estado
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->state ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Cidade
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $patient->address->city ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.patients.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                        Voltar
                    </a>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        @can('patients.update')
                            <a href="{{ workspace_route('tenant.patients.edit', $patient->id) }}"
                               class="btn btn-primary inline-flex items-center">
                                <x-icon name="pencil-outline" size="text-sm" class="mr-2" />
                                Editar
                            </a>
                        @endcan
                        <a href="{{ workspace_route('tenant.patients.login.form', $patient->id) }}"
                           class="btn btn-outline inline-flex items-center">
                            <x-icon name="key-outline" size="text-sm" class="mr-2" />
                            {{ isset($patient->login) ? 'Editar Login' : 'Criar Login' }}
                        </a>
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
                                <x-icon name="trash-can-outline" size="text-sm" class="mr-2" />
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection