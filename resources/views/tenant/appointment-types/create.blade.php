@extends('layouts.tailadmin.app')

@section('title', 'Criar Tipo de Consulta')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">Tipos de Consulta</a>
                    </li>
                    <li class="flex items-center gap-2 text-gray-900 dark:text-white font-semibold">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
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
                    <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="btn-patient-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7v10a2 2 0 002-2h4v-4a2 2 0 00-2-2h-4v-4a2 2 0 00-2-2z"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002 2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Salvar Tipo de Consulta
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <style>
        /* Botões padrão com suporte a modo claro e escuro */
        .btn-patient-primary {
            background-color: #2563eb;
            color: white;
            border: 1px solid #d1d5db;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-patient-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-patient-secondary {
            background-color: transparent;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-patient-secondary:hover {
            background-color: #f9fafb;
        }
        
        /* Modo escuro via preferência do sistema */
        @media (prefers-color-scheme: dark) {
            .btn-patient-primary {
                background-color: transparent;
                color: white;
                border-color: #d1d5db;
            }
            
            .btn-patient-primary:hover {
                background-color: #1f2937;
            }
            
            .btn-patient-secondary {
                background-color: transparent;
                color: white;
                border-color: #d1d5db;
            }
            
            .btn-patient-secondary:hover {
                background-color: #1f2937;
            }
        }
        
        /* Modo escuro via classe */
        .dark .btn-patient-primary {
            background-color: transparent;
            color: white;
            border-color: #d1d5db;
        }
        
        .dark .btn-patient-primary:hover {
            background-color: #1f2937;
        }
        
        .dark .btn-patient-secondary {
            background-color: transparent;
            color: white;
            border-color: #d1d5db;
        }
        
        .dark .btn-patient-secondary:hover {
            background-color: #1f2937;
        }
    </style>
@endpush

@endsection

