@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Conta OAuth')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-account-key text-primary me-2"></i>
            Detalhes da Conta OAuth
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.oauth-accounts.index') }}">Contas OAuth</a>
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
                            <i class="mdi mdi-account-key text-primary me-2"></i>
                            Informações da Conta OAuth
                        </h4>
                        <div>
                            <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                        </div>
                    </div>

                    {{-- Informações Principais --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $oauthAccount->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-link me-1"></i> Integração
                                </label>
                                <p class="mb-0 fw-semibold">{{ $oauthAccount->integration->key ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-account me-1"></i> Usuário ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $oauthAccount->user_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-expire me-1"></i> Expira em
                                </label>
                                <p class="mb-0 fw-semibold">
                                    {{ $oauthAccount->expires_at ? $oauthAccount->expires_at->format('d/m/Y H:i') : 'N/A' }}
                                    @if($oauthAccount->expires_at && $oauthAccount->expires_at->isPast())
                                        <span class="badge bg-danger ms-2">Expirado</span>
                                    @elseif($oauthAccount->expires_at && $oauthAccount->expires_at->isFuture())
                                        <span class="badge bg-success ms-2">Válido</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Informações Adicionais --}}
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-plus me-1"></i> Criado em
                                </label>
                                <p class="mb-0">{{ $oauthAccount->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $oauthAccount->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

