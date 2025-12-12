@extends('layouts.freedash.app')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-dark font-weight-medium mb-1">
                    <i class="fab fa-whatsapp text-success me-2"></i> Z-API - Enviar Notificação
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Z-API</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fab fa-whatsapp text-success me-2"></i>
                            Enviar Mensagem via Z-API
                        </h5>
                    </div>

                    <div class="card-body">
                        @if (!$isZApi)
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Atenção!</strong> O provedor configurado atualmente é: <strong>{{ $providerName }}</strong>.
                                Para usar este módulo, configure o sistema para usar Z-API no arquivo <code>.env</code>:
                                <pre class="mt-2 mb-0"><code>WHATSAPP_PROVIDER=zapi
ZAPI_API_URL=https://api.z-api.io
ZAPI_TOKEN=seu_token
ZAPI_INSTANCE_ID=seu_instance_id</code></pre>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @else
                            {{-- Mostra informações de configuração (sem expor valores sensíveis) --}}
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Configuração Z-API:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>API URL:</strong> {{ $configInfo['api_url'] ?? 'não configurado' }}</li>
                                    <li><strong>Token:</strong> {{ ($configInfo['token_set'] ?? false) ? '✓ Configurado' : '✗ Não configurado' }}</li>
                                    <li><strong>Instance ID:</strong> {{ $configInfo['instance_id'] ?? 'não configurado' }}</li>
                                </ul>
                                <small class="text-muted d-block mt-2">
                                    Se estiver tendo problemas, verifique se o Instance ID e Token estão corretos no arquivo <code>.env</code>
                                </small>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('Platform.zapi.send') }}" id="zapiForm">
                            @csrf

                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-1"></i> Número do Telefone
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" 
                                    name="phone" 
                                    value="{{ old('phone') }}"
                                    placeholder="Ex: 11999999999 ou (11) 99999-9999"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Digite o número com DDD. Exemplos: 11999999999, (11) 99999-9999, 11 99999-9999
                                </small>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-1"></i> Mensagem
                                </label>
                                <textarea 
                                    class="form-control @error('message') is-invalid @enderror" 
                                    id="message" 
                                    name="message" 
                                    rows="8"
                                    placeholder="Digite sua mensagem aqui..."
                                    required
                                    maxlength="4096"
                                >{{ old('message') }}</textarea>
                                <small class="form-text text-muted">
                                    Máximo de 4096 caracteres. 
                                    <span id="charCount">0</span>/4096 caracteres
                                </small>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('Platform.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Voltar
                                </a>
                                <button 
                                    type="submit" 
                                    class="btn btn-success"
                                    @if(!$isZApi) disabled @endif
                                >
                                    <i class="fab fa-whatsapp me-1"></i>
                                    Enviar Mensagem
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($isZApi)
                    <div class="card shadow-sm border-0 mt-3">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i> Informações
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>O número será formatado automaticamente para o padrão esperado pela Z-API</li>
                                <li>A mensagem será enviada imediatamente após o envio do formulário</li>
                                <li>O resultado do envio será registrado nos logs do sistema</li>
                                <li>Certifique-se de que a instância Z-API está conectada e ativa</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        // Contador de caracteres
        document.addEventListener('DOMContentLoaded', function() {
            const messageTextarea = document.getElementById('message');
            const charCount = document.getElementById('charCount');

            if (messageTextarea && charCount) {
                function updateCharCount() {
                    const length = messageTextarea.value.length;
                    charCount.textContent = length;
                    
                    if (length > 4000) {
                        charCount.classList.add('text-danger');
                    } else {
                        charCount.classList.remove('text-danger');
                    }
                }

                messageTextarea.addEventListener('input', updateCharCount);
                updateCharCount(); // Atualiza na carga inicial
            }

            // Formatação básica do telefone (remove caracteres não numéricos)
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    // Permite números, espaços, parênteses, hífens e +
                    // A formatação completa será feita no backend
                });
            }
        });
    </script>
@endpush

