@extends('layouts.connect_plus.app')

@section('title', 'Criar Tipo de Consulta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Tipo de Consulta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.appointment-types.index') }}">Tipos de Consulta</a>
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
                                <i class="mdi mdi-calendar-plus text-primary me-2"></i>
                                Novo Tipo de Consulta
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para cadastrar um novo tipo de consulta</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.appointment-types.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Informações do Tipo de Consulta --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações do Tipo de Consulta
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
                                               placeholder="Ex: Consulta Médica, Retorno, etc." required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-outline me-1"></i>
                                            Duração (minutos) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control @error('duration_min') is-invalid @enderror" 
                                               name="duration_min" value="{{ old('duration_min') }}" 
                                               min="1" placeholder="30" required>
                                        <small class="form-text text-muted">Tempo de duração da consulta</small>
                                        @error('duration_min')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-toggle-switch me-1"></i>
                                            Status
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
                            <a href="{{ route('tenant.appointment-types.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Tipo de Consulta
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .card-title {
        font-weight: 600;
    }
    h5.text-primary {
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
</style>
@endpush

@endsection

