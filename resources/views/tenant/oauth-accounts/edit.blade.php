@extends('layouts.connect_plus.app')

@section('title', 'Editar Conta OAuth')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Conta OAuth </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.oauth-accounts.index') }}">Contas OAuth</a>
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
                                <i class="mdi mdi-account-key-edit text-primary me-2"></i>
                                Editar Conta OAuth
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações da conta OAuth abaixo</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops!</strong> Verifique os erros abaixo:
                            <ul class="mt-2 mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('tenant.oauth-accounts.update', $oauthAccount->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Informações Básicas --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações Básicas
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-link me-1"></i>
                                            Integração
                                        </label>
                                        <select name="integration_id" class="form-control" disabled>
                                            <option value="{{ $oauthAccount->integration_id }}">
                                                {{ $oauthAccount->integration->key ?? 'N/A' }}
                                            </option>
                                        </select>
                                        <small class="form-text text-muted">A integração não pode ser alterada após a criação.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Usuário
                                        </label>
                                        <select name="user_id" class="form-control" disabled>
                                            <option value="{{ $oauthAccount->user_id }}">
                                                @if($oauthAccount->user)
                                                    {{ $oauthAccount->user->name_full ?? $oauthAccount->user->name }} ({{ $oauthAccount->user->email ?? 'Sem email' }})
                                                @else
                                                    Usuário #{{ $oauthAccount->user_id }}
                                                @endif
                                            </option>
                                        </select>
                                        <small class="form-text text-muted">O usuário não pode ser alterado após a criação.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Tokens OAuth --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-key me-2"></i>
                                Tokens OAuth
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-key-variant me-1"></i>
                                            Access Token <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('access_token') is-invalid @enderror" 
                                                  name="access_token" 
                                                  rows="3" 
                                                  required
                                                  placeholder="Token de acesso OAuth">{{ old('access_token', $oauthAccount->access_token) }}</textarea>
                                        <small class="form-text text-muted">Token de acesso fornecido pelo provedor OAuth</small>
                                        @error('access_token')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-key-refresh me-1"></i>
                                            Refresh Token
                                        </label>
                                        <textarea class="form-control @error('refresh_token') is-invalid @enderror" 
                                                  name="refresh_token" 
                                                  rows="3"
                                                  placeholder="Token de atualização OAuth">{{ old('refresh_token', $oauthAccount->refresh_token) }}</textarea>
                                        <small class="form-text text-muted">Token usado para renovar o access token quando expirar</small>
                                        @error('refresh_token')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-alert me-1"></i>
                                            Expira em
                                        </label>
                                        <input type="datetime-local" 
                                               class="form-control @error('expires_at') is-invalid @enderror" 
                                               name="expires_at" 
                                               value="{{ old('expires_at', $oauthAccount->expires_at ? $oauthAccount->expires_at->format('Y-m-d\TH:i') : '') }}">
                                        <small class="form-text text-muted">Data e hora de expiração do token</small>
                                        @error('expires_at')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.oauth-accounts.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Atualizar Conta OAuth
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
