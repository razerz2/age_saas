@extends('layouts.connect_plus.app')

@section('title', 'Criar Especialidade')

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0"> Criar Especialidade </h3>
            <x-help-button module="specialties" />
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.specialties.index') }}">Especialidades</a>
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
                                <i class="mdi mdi-stethoscope text-primary me-2"></i>
                                Nova Especialidade
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para cadastrar uma nova especialidade médica</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ workspace_route('tenant.specialties.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Dados da Especialidade --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações da Especialidade
                            </h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-tag me-1"></i>
                                            Nome <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" 
                                               placeholder="Ex: Cardiologia, Pediatria, etc." required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-barcode me-1"></i>
                                            Código
                                        </label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                               name="code" value="{{ old('code') }}" 
                                               maxlength="50" placeholder="Código CBO (opcional)">
                                        <small class="form-text text-muted">Código CBO da especialidade</small>
                                        @error('code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(tenant_setting('professional.customization_enabled'))
                            {{-- Seção: Personalização de Rótulos --}}
                            <div class="mb-4">
                                <h5 class="mb-3 text-primary">
                                    <i class="mdi mdi-tag-text-outline me-2"></i>
                                    Personalização de Rótulos
                                </h5>
                                <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <div>
                                        <small>Configure rótulos personalizados para esta especialidade. Estes valores sobrescrevem os rótulos globais.</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="fw-semibold">
                                                <i class="mdi mdi-tag me-1"></i>
                                                Rótulo Singular
                                            </label>
                                            <input type="text" class="form-control @error('label_singular') is-invalid @enderror" 
                                                   name="label_singular" value="{{ old('label_singular') }}" 
                                                   placeholder="Ex: Psicólogo, Dentista"
                                                   maxlength="50">
                                            <small class="form-text text-muted">Exemplo: "Psicólogo" ou "Dentista"</small>
                                            @error('label_singular')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="fw-semibold">
                                                <i class="mdi mdi-tag-multiple me-1"></i>
                                                Rótulo Plural
                                            </label>
                                            <input type="text" class="form-control @error('label_plural') is-invalid @enderror" 
                                                   name="label_plural" value="{{ old('label_plural') }}" 
                                                   placeholder="Ex: Psicólogos, Dentistas"
                                                   maxlength="50">
                                            <small class="form-text text-muted">Exemplo: "Psicólogos" ou "Dentistas"</small>
                                            @error('label_plural')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="fw-semibold">
                                                <i class="mdi mdi-card-account-details me-1"></i>
                                                Rótulo de Registro
                                            </label>
                                            <input type="text" class="form-control @error('registration_label') is-invalid @enderror" 
                                                   name="registration_label" value="{{ old('registration_label') }}" 
                                                   placeholder="Ex: CRP, CRO"
                                                   maxlength="50">
                                            <small class="form-text text-muted">Exemplo: "CRP" ou "CRO"</small>
                                            @error('registration_label')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ workspace_route('tenant.specialties.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Especialidade
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
@endpush

@endsection
