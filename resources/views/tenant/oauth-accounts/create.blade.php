@extends('layouts.connect_plus.app')

@section('title', 'Criar Conta OAuth')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Conta OAuth </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.oauth-accounts.index') }}">Contas OAuth</a>
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
                                <i class="mdi mdi-account-key-plus text-primary me-2"></i>
                                Nova Conta OAuth
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar uma nova conta OAuth</p>
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

                    <form class="forms-sample" action="{{ workspace_route('tenant.oauth-accounts.store') }}" method="POST">
                        @csrf

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
                                            Integração <span class="text-danger">*</span>
                                        </label>
                                        <select name="integration_id" class="form-control @error('integration_id') is-invalid @enderror" required>
                                            <option value="">Selecione uma integração</option>
                                            @foreach ($integrations as $integration)
                                                <option value="{{ $integration->id }}" {{ old('integration_id') == $integration->id ? 'selected' : '' }}>
                                                    {{ $integration->key }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('integration_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Usuário <span class="text-danger">*</span>
                                        </label>
                                        <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                            <option value="">Selecione um usuário</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name_full ?? $user->name }} ({{ $user->email ?? 'Sem email' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
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
                                                  placeholder="Token de acesso OAuth">{{ old('access_token') }}</textarea>
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
                                                  placeholder="Token de atualização OAuth">{{ old('refresh_token') }}</textarea>
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
                                               value="{{ old('expires_at') }}">
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
                            <a href="{{ workspace_route('tenant.oauth-accounts.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Conta OAuth
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

