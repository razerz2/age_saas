@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Especialidade')
@section('page', 'specialties')

@section('content')
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="medical-bag" size="text-xl" class="text-blue-600" />
                    Detalhes da Especialidade
                </h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Dashboard</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.specialties.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Especialidades</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações da Especialidade</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">ID</p>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $specialty->id }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nome</p>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $specialty->name }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Código</p>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $specialty->code ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-6">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Informações de sistema</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Criado em</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $specialty->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Atualizado em</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $specialty->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ workspace_route('tenant.specialties.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                        Voltar
                    </a>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ workspace_route('tenant.specialties.edit', $specialty->id) }}" class="btn btn-outline inline-flex items-center tenant-action-edit">
                            <x-icon name="pencil-outline" size="text-sm" class="mr-2" />
                            Editar
                        </a>

                        <form
                            id="specialties-delete-form-{{ $specialty->id }}"
                            action="{{ workspace_route('tenant.specialties.destroy', $specialty->id) }}"
                            method="POST"
                            class="inline"
                            data-specialty-name="{{ $specialty->name }}"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="button" class="btn btn-outline tenant-action-delete inline-flex items-center"
                                data-delete-trigger="1"
                                data-delete-form="#specialties-delete-form-{{ $specialty->id }}"
                                data-delete-title="Excluir especialidade"
                                data-delete-message="Tem certeza que deseja excluir {{ $specialty->name }}?">
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

