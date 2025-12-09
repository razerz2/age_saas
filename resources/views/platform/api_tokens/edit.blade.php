@extends('layouts.freedash.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-key text-primary me-2"></i> Editar Token de API
                        </h4>
                        <a href="{{ route('Platform.tenants.api-tokens.index', $tenant) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informação:</strong> Você pode visualizar o token completo na <a href="{{ route('Platform.tenants.api-tokens.show', [$tenant, $token]) }}" class="alert-link">página de detalhes</a>.
                        </div>

                        <form action="{{ route('Platform.tenants.api-tokens.update', [$tenant, $token]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nome do Token <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $token->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="active" 
                                           name="active" 
                                           value="1"
                                           {{ old('active', $token->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Token Ativo
                                    </label>
                                </div>
                                <small class="form-text text-muted">Tokens inativos não podem ser usados para autenticação.</small>
                            </div>

                            <div class="mb-3">
                                <label for="expires_at" class="form-label">Data de Expiração</label>
                                <input type="datetime-local" 
                                       class="form-control @error('expires_at') is-invalid @enderror" 
                                       id="expires_at" 
                                       name="expires_at" 
                                       value="{{ old('expires_at', $token->expires_at ? $token->expires_at->format('Y-m-d\TH:i') : '') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Deixe em branco para remover a expiração.</small>
                            </div>

                            <div class="mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Informações do Token</h6>
                                        <p class="mb-1"><strong>Criado por:</strong> {{ $token->creator->name ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Criado em:</strong> {{ $token->created_at->format('d/m/Y H:i') }}</p>
                                        @if ($token->last_used_at)
                                            <p class="mb-0"><strong>Último uso:</strong> {{ $token->last_used_at->format('d/m/Y H:i') }}</p>
                                        @else
                                            <p class="mb-0"><strong>Último uso:</strong> <span class="text-muted">Nunca usado</span></p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('Platform.tenants.api-tokens.index', $tenant) }}" class="btn btn-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

