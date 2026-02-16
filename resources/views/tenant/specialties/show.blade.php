@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Especialidade')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    Detalhes da Especialidade
                </h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.specialties.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Especialidades</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex items-center justify-end gap-3 flex-nowrap">
                <a href="{{ workspace_route('tenant.specialties.edit', $specialty->id) }}" class="btn-patient-primary inline-flex items-center gap-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ workspace_route('tenant.specialties.index') }}" class="btn-patient-secondary inline-flex items-center gap-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
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
        </div>
    </div>
@endsection
