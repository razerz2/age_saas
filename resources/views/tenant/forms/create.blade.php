@extends('layouts.connect_plus.app')

@section('title', 'Criar Formulário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Formulário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
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
                                <i class="mdi mdi-file-document-plus text-primary me-2"></i>
                                Novo Formulário
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar um novo formulário</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.forms.store') }}" method="POST">
                        @csrf

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
                                               name="name" value="{{ old('name') }}" 
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
                                                  placeholder="Digite uma descrição para o formulário (opcional)">{{ old('description') }}</textarea>
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
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
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
                                        <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror" disabled>
                                            <option value="">Primeiro selecione um médico</option>
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
                                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.forms.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Formulário
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-forms.css') }}" rel="stylesheet">
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
            fetch(`{{ route('tenant.forms.doctors.specialties', ['doctorId' => '__DOCTOR_ID__']) }}`.replace('__DOCTOR_ID__', doctorId))
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

