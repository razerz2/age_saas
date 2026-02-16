@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Paciente')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ workspace_route('tenant.patients.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            Pacientes
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
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
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Ativo
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                Inativo
            </span>
        @endif
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações do Paciente</h2>
            </div>
        </div>
        
        <div class="p-6 space-y-8">
            <!-- Informações Pessoais -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
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
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                    </svg>
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
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
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
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Criado em
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                            </svg>
                            Atualizado em
                        </label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between flex-nowrap mt-6 border-t pt-4">
                <div>
                    <a href="{{ workspace_route('tenant.patients.index') }}"
                       class="btn-patient-secondary inline-flex items-center gap-2 px-4 py-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    @can('patients.update')
                        <a href="{{ workspace_route('tenant.patients.edit', $patient->id) }}"
                           class="btn-patient-primary inline-flex items-center gap-2 px-4 py-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar
                        </a>
                    @endcan
                    <form action="{{ workspace_route('tenant.patients.destroy', $patient->id) }}" method="POST" class="inline"
                          onsubmit="event.preventDefault(); confirmAction({ title: 'Excluir paciente', message: 'Tem certeza que deseja excluir este paciente? Esta ação não pode ser desfeita.', confirmText: 'Excluir', cancelText: 'Cancelar', type: 'error', onConfirm: () => event.target.submit() }); return false;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn-patient-secondary inline-flex items-center gap-2 px-4 py-2 text-red-600 hover:text-red-900">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
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
            text-decoration: none;
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
                background-color: #1d4ed8;
                border-color: #1d4ed8;
                color: white;
            }
            
            .btn-patient-primary:hover {
                background-color: #1d4ed8;
                border-color: #1d4ed8;
            }
            
            .btn-patient-secondary {
                background-color: #111827;
                border-color: #1f2937;
                color: #f9fafb;
            }
            
            .btn-patient-secondary:hover {
                background-color: #1f2937;
                border-color: #4b5563;
            }
        }
        
        /* For TailAdmin dark mode class */
        .dark .btn-patient-primary {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: white;
        }
        
        .dark .btn-patient-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .dark .btn-patient-secondary {
            background-color: #111827;
            border-color: #1f2937;
            color: #f9fafb;
        }
        
        .dark .btn-patient-secondary:hover {
            background-color: #1f2937;
            border-color: #4b5563;
        }
    </style>
@endpush
