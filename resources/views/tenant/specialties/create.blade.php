@extends('layouts.connect_plus.app')

@section('title', 'Criar Especialidade')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Especialidade </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.specialties.index') }}">Especialidades</a>
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

                    <form class="forms-sample" action="{{ route('tenant.specialties.store') }}" method="POST">
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

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.specialties.index') }}" class="btn btn-light">
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
