@extends('layouts.tailadmin.app')

@section('title', 'Editar Formulário')

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0"> Editar Formulário </h3>
            <x-help-button module="forms" />
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-file-document-edit text-primary me-2"></i>
                                Editar Formulário
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações do formulário abaixo</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ workspace_route('tenant.forms.update', $form->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Informações do Formulário --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações do Formulário
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-tag me-1"></i>
                                            Nome <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', $form->name) }}" 
                                               placeholder="Digite o nome do formulário" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-text me-1"></i>
                                            Descrição
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  name="description" rows="4" 
                                                  placeholder="Digite uma descrição para o formulário (opcional)">{{ old('description', $form->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Associação --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-link me-2"></i>
                                Associação
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $form->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Selecione o médico para o qual o formulário será criado</small>
                                        @error('doctor_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-stethoscope me-1"></i>
                                            Especialidade
                                        </label>
                                        <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror">
                                            <option value="">Carregando especialidades...</option>
                                        </select>
                                        <small class="form-text text-muted">Selecione uma especialidade relacionada ao médico (opcional)</small>
                                        @error('specialty_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Status --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-toggle-switch me-2"></i>
                                Status
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-check-circle me-1"></i>
                                            Status do Formulário
                                        </label>
                                        <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ old('is_active', $form->is_active) == 1 ? 'selected' : '' }}>Ativo</option>
                                            <option value="0" {{ old('is_active', $form->is_active) == 0 ? 'selected' : '' }}>Inativo</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="flex flex-col gap-3 pt-3 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
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
                                Atualizar Formulário
                            </button>
                        </div>
                    </form>

                </div>
            </div>
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
        const currentDoctorId = '{{ old("doctor_id", $form->doctor_id) }}';
        const currentSpecialtyId = '{{ old("specialty_id", $form->specialty_id) }}';

        function loadSpecialties(doctorId, selectedSpecialtyId = null) {
            specialtySelect.innerHTML = '<option value="">Carregando especialidades...</option>';
            specialtySelect.disabled = true;

            if (!doctorId) {
                specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
                return;
            }

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
                            if (selectedSpecialtyId && selectedSpecialtyId === specialty.id) {
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
        }

        doctorSelect.addEventListener('change', function() {
            loadSpecialties(this.value);
        });

        // Carregar especialidades do médico atual ao carregar a página
        if (currentDoctorId) {
            loadSpecialties(currentDoctorId, currentSpecialtyId);
        } else {
            specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
            specialtySelect.disabled = true;
        }
    });
</script>
@endpush

@endsection
