@extends('layouts.freedash.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-key text-primary me-2"></i> Detalhes do Token de API
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('Platform.tenants.api-tokens.index', $tenant) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                            <a href="{{ route('Platform.tenants.api-tokens.edit', [$tenant, $token]) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">Tenant:</label>
                            <p class="mb-0">{{ $tenant->trade_name ?? $tenant->legal_name }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nome do Token:</label>
                            <p class="mb-0">{{ $token->name }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Status:</label>
                            <p class="mb-0">
                                @if ($token->active)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Data de Expiração:</label>
                            <p class="mb-0">
                                @if ($token->expires_at)
                                    @if ($token->expires_at->isPast())
                                        <span class="text-danger">Expirado em {{ $token->expires_at->format('d/m/Y H:i') }}</span>
                                    @else
                                        {{ $token->expires_at->format('d/m/Y H:i') }}
                                    @endif
                                @else
                                    <span class="text-muted">Sem expiração</span>
                                @endif
                            </p>
                        </div>

                        @if ($decryptedToken)
                            <div class="mb-4">
                                <label for="token_value" class="form-label fw-bold">Token de API:</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control font-monospace" 
                                           id="token_value" 
                                           value="{{ $decryptedToken }}" 
                                           readonly>
                                    <button class="btn btn-primary" type="button" onclick="copyToken()">
                                        <i class="fas fa-copy me-1"></i> Copiar
                                    </button>
                                </div>
                                <small class="form-text text-muted">Clique em "Copiar" para copiar o token para a área de transferência.</small>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Token não disponível para visualização.
                            </div>
                        @endif

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Informações Adicionais</h6>
                                <p class="mb-1"><strong>Criado por:</strong> {{ $token->creator->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Criado em:</strong> {{ $token->created_at->format('d/m/Y H:i') }}</p>
                                @if ($token->last_used_at)
                                    <p class="mb-1"><strong>Último uso:</strong> {{ $token->last_used_at->format('d/m/Y H:i') }}</p>
                                    @if ($token->last_ip)
                                        <p class="mb-0"><strong>Último IP:</strong> {{ $token->last_ip }}</p>
                                    @endif
                                @else
                                    <p class="mb-0"><strong>Último uso:</strong> <span class="text-muted">Nunca usado</span></p>
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Como usar este token:</h6>
                            <p class="mb-0">
                                Use este token no header <code>Authorization: Bearer {token}</code> ao fazer requisições para a API de bots.
                                <br>
                                <strong>Base URL:</strong> <code>{{ url('/api/bot') }}</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        function copyToken() {
            const tokenInput = document.getElementById('token_value');
            tokenInput.select();
            tokenInput.setSelectionRange(0, 99999); // Para mobile
            
            try {
                document.execCommand('copy');
                
                // Feedback visual
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check me-1"></i> Copiado!';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 2000);
            } catch (err) {
                alert('Erro ao copiar. Por favor, copie manualmente.');
            }
        }
    </script>
@endpush

