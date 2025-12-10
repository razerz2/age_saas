@extends('layouts.connect_plus.app')

@section('title', 'Link de Agendamento Público')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-link-variant"></i>
        </span>
        Link de Agendamento Público
    </h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Link de Agendamento</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-4">Link de Agendamento Público</h4>
                <p class="text-muted mb-4">
                    Compartilhe este link com seus pacientes para que eles possam agendar consultas diretamente pela internet.
                </p>

                @if($publicBookingUrl)
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="mdi mdi-link-variant me-2"></i>Seu Link de Agendamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Link para compartilhar:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="publicBookingLink" 
                                           value="{{ $publicBookingUrl }}" readonly>
                                    <button class="btn btn-primary" type="button" onclick="copyPublicBookingLink()">
                                        <i class="mdi mdi-content-copy me-2"></i>Copiar Link
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    Clique em "Copiar Link" para copiar o endereço completo
                                </small>
                            </div>

                            <div class="alert alert-success d-flex align-items-start" id="copySuccessAlert" role="alert" style="display: none;">
                                <i class="mdi mdi-check-circle-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                                <div class="flex-grow-1">
                                    <strong class="d-block mb-2">Link copiado com sucesso!</strong>
                                    <p class="mb-0" style="font-size: 0.9rem;">
                                        O link foi copiado para a área de transferência. Agora você pode colar em qualquer lugar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning" role="alert">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        <strong>Atenção:</strong> Não foi possível gerar o link de agendamento público. Verifique se o tenant está configurado corretamente.
                    </div>
                @endif

                <div class="card border shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="mdi mdi-information-outline me-2"></i>Sobre o Link de Agendamento Público
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="mdi mdi-calendar-check me-2 text-primary"></i>Como funciona?
                            </h6>
                            <p class="text-muted mb-3">
                                O link de agendamento público permite que seus pacientes agendem consultas diretamente pela internet, 
                                sem precisar entrar em contato por telefone ou WhatsApp. É uma forma prática e moderna de receber agendamentos.
                            </p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Fácil de usar:</strong> Os pacientes acessam o link e seguem um processo simples e intuitivo
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Disponível 24/7:</strong> Seus pacientes podem agendar a qualquer hora do dia ou da noite
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Reduz filas:</strong> Diminui o volume de ligações e mensagens para agendamento
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Organização automática:</strong> Os agendamentos são registrados diretamente no sistema
                                </li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="mdi mdi-share-variant me-2 text-primary"></i>Onde compartilhar?
                            </h6>
                            <p class="text-muted mb-3">
                                Você pode adicionar este link em vários lugares para facilitar o acesso dos seus pacientes:
                            </p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="mdi mdi-facebook me-3 text-primary" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong class="d-block">Redes Sociais</strong>
                                            <small class="text-muted">Adicione o link na bio do Instagram, Facebook, LinkedIn ou outras redes sociais da sua clínica ou consultório</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="mdi mdi-whatsapp me-3 text-success" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong class="d-block">WhatsApp</strong>
                                            <small class="text-muted">Envie o link diretamente para pacientes ou adicione em mensagens automáticas</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="mdi mdi-email me-3 text-danger" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong class="d-block">E-mail</strong>
                                            <small class="text-muted">Inclua o link em assinaturas de e-mail ou em campanhas de marketing</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="mdi mdi-web me-3 text-info" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong class="d-block">Site ou Blog</strong>
                                            <small class="text-muted">Adicione um botão ou link no seu site para facilitar o agendamento</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-start" role="alert">
                            <i class="mdi mdi-lightbulb-outline me-3" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-2">Dica:</strong>
                                <p class="mb-0" style="font-size: 0.9rem;">
                                    Para médicos autônomos, clínicas e empresas, este link é uma excelente forma de profissionalizar 
                                    o atendimento e facilitar o processo de agendamento. Quanto mais fácil for para o paciente agendar, 
                                    mais consultas você receberá!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Função para copiar o link de agendamento público
    function copyPublicBookingLink() {
        const linkInput = document.getElementById('publicBookingLink');
        if (!linkInput) {
            alert('Link não encontrado.');
            return;
        }

        const link = linkInput.value;

        // Tentar usar a API Clipboard moderna
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function() {
                showCopySuccess();
            }).catch(function(err) {
                console.error('Erro ao copiar:', err);
                fallbackCopy(link);
            });
        } else {
            // Fallback para navegadores mais antigos
            fallbackCopy(link);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            console.error('Erro ao copiar:', err);
            alert('Erro ao copiar. Por favor, copie manualmente.');
        }
        
        document.body.removeChild(textarea);
    }

    function showCopySuccess() {
        const alert = document.getElementById('copySuccessAlert');
        if (alert) {
            alert.style.display = 'flex';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 3000);
        }
    }
</script>
@endpush
@endsection

