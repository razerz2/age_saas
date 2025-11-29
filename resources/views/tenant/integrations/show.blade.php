@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Integração')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-link text-primary me-2"></i>
            Detalhes da Integração
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.integrations.index') }}">Integrações</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- Header do Card --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-link text-primary me-2"></i>
                            Informações da Integração
                        </h4>
                        <div>
                            <a href="{{ route('tenant.integrations.edit', $integration->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ route('tenant.integrations.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if ($integration->is_enabled)
                            <span class="badge bg-success px-3 py-2">
                                <i class="mdi mdi-check-circle me-1"></i> Habilitado
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2">
                                <i class="mdi mdi-close-circle me-1"></i> Desabilitado
                            </span>
                        @endif
                    </div>

                    {{-- Informações Principais --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $integration->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-key me-1"></i> Chave
                                </label>
                                <p class="mb-0 fw-semibold">{{ $integration->key }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Configuração --}}
                    <div class="mb-4">
                        <label class="text-muted small mb-2 d-block">
                            <i class="mdi mdi-cog me-1"></i> Configuração
                        </label>
                        <div class="border rounded p-3 bg-light">
                            <pre class="mb-0" style="max-height: 300px; overflow-y: auto;">{{ is_array($integration->config) ? json_encode($integration->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $integration->config }}</pre>
                        </div>
                    </div>

                    {{-- Informações Adicionais --}}
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-plus me-1"></i> Criado em
                                </label>
                                <p class="mb-0">{{ $integration->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $integration->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

