@extends('layouts.tailadmin.app')

@section('title', 'Criar Formulário')

@section('content')

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ workspace_route('tenant.forms.index') }}" class="hover:text-blue-600 dark:hover:text-white">Formulários</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="forms" />
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">Novo Formulário</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preencha os dados abaixo para criar um novo formulário</p>
            </div>

            <form action="{{ workspace_route('tenant.forms.store') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações do Formulário</h5>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                                   name="name" value="{{ old('name') }}"
                                   placeholder="Digite o nome do formulário" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Descrição
                            </label>
                            <textarea class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror"
                                      name="description" rows="4"
                                      placeholder="Digite uma descrição para o formulário (opcional)">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Associação</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Selecione o médico para o qual o formulário será criado</p>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Especialidade
                            </label>
                            <select name="specialty_id" id="specialty_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('specialty_id') border-red-500 @enderror" disabled>
                                <option value="">Primeiro selecione um médico</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Selecione uma especialidade relacionada ao médico (opcional)</p>
                            @error('specialty_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status do Formulário
                            </label>
                            <select name="is_active" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('is_active') border-red-500 @enderror">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.forms.index') }}" class="btn-patient-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                        </svg>
                        Salvar Formulário
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-forms.css') }}" rel="stylesheet">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const doctorSelect = document.getElementById('doctor_id');
        const specialtySelect = document.getElementById('specialty_id');
        const oldSpecialtyId = '{{ old("specialty_id") }}';

        doctorSelect.addEventListener('change', function() {
            const doctorId = this.value;

            // Limpar e desabilitar o select de especialidades
            specialtySelect.innerHTML = '<option value="">Carregando especialidades...</option>';
            specialtySelect.disabled = true;

            if (!doctorId) {
                specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
                return;
            }

            // Buscar especialidades do médico
            fetch(`{{ workspace_route('tenant.forms.doctors.specialties', ['doctorId' => '__DOCTOR_ID__']) }}`.replace('__DOCTOR_ID__', doctorId))
                .then(response => response.json())
                .then(data => {
                    specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';
                    
                    if (data.length === 0) {
                        specialtySelect.innerHTML = '<option value="">Este médico não possui especialidades cadastradas</option>';
                    } else {
                        data.forEach(specialty => {
                            const option = document.createElement('option');
                            option.value = specialty.id;
                            option.textContent = specialty.name;
                            if (oldSpecialtyId && oldSpecialtyId === specialty.id) {
                                option.selected = true;
                            }
                            specialtySelect.appendChild(option);
                        });
                        specialtySelect.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar especialidades:', error);
                    specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
                });
        });

        // Se já houver um médico selecionado (old value), carregar suas especialidades
        if (doctorSelect.value && oldSpecialtyId) {
            doctorSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush

@endsection

