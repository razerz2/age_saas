@extends('layouts.tailadmin.app')

@section('title', 'Editar Formulário')
@section('page', 'forms')

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
                                <x-icon name="file-document-edit" class=" text-primary me-2" />
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
                                <x-icon name="information-outline" class=" me-2" />
                                Informações do Formulário
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <x-icon name="tag" class=" me-1" />
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
                                            <x-icon name="text" class=" me-1" />
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
                                <x-icon name="link" class=" me-2" />
                                Associação
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <x-icon name="doctor" class=" me-1" />
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" data-specialties-url-template="{{ workspace_route('tenant.forms.doctors.specialties', ['doctorId' => '__DOCTOR_ID__']) }}" required>
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
                                            <x-icon name="stethoscope" class=" me-1" />
                                            Especialidade
                                        </label>
                                        <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror" data-initial-specialty-id="{{ old('specialty_id', $form->specialty_id) }}">
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
                                <x-icon name="toggle-switch" class=" me-2" />
                                Status
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <x-icon name="check-circle" class=" me-1" />
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
                            <a href="{{ workspace_route('tenant.forms.index') }}" class="btn btn-outline">
                                <x-icon name="information-outline" class="w-4 h-4 mr-2" />
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <x-icon name="information-outline" class="w-4 h-4 mr-2" />
                                Atualizar Formulário
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
