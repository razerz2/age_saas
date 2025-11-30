@extends('layouts.connect_plus.app')

@section('title', 'Criar Integração')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Integração </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.integrations.index') }}">Integrações</a>
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
                                <i class="mdi mdi-link-plus text-primary me-2"></i>
                                Nova Integração
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar uma nova integração</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.integrations.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Informações da Integração --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações da Integração
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-key me-1"></i>
                                            Chave <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('key') is-invalid @enderror" 
                                               name="key" value="{{ old('key') }}" 
                                               placeholder="Ex: google_calendar, whatsapp, etc." required>
                                        <small class="form-text text-muted">Identificador único da integração</small>
                                        @error('key')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-toggle-switch me-1"></i>
                                            Status
                                        </label>
                                        <select name="is_enabled" class="form-control @error('is_enabled') is-invalid @enderror">
                                            <option value="1" {{ old('is_enabled', '1') == '1' ? 'selected' : '' }}>Habilitado</option>
                                            <option value="0" {{ old('is_enabled') == '0' ? 'selected' : '' }}>Desabilitado</option>
                                        </select>
                                        @error('is_enabled')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Configuração --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-cog-outline me-2"></i>
                                Configuração
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-code-json me-1"></i>
                                            Configuração (JSON)
                                        </label>
                                        <textarea class="form-control @error('config') is-invalid @enderror" 
                                                  name="config" rows="6" 
                                                  placeholder='{"key": "value", "api_key": "your_api_key"}' 
                                                  style="font-family: monospace;">{{ old('config') }}</textarea>
                                        <small class="form-text text-muted">Configure os parâmetros da integração em formato JSON</small>
                                        @error('config')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.integrations.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Integração
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

