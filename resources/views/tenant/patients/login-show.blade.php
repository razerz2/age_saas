@extends('layouts.connect_plus.app')

@section('title', 'Informações de Login do Paciente')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-key"></i>
        </span>
        Informações de Login do Paciente
    </h3>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Dados do Paciente</h4>
                <div class="mb-3">
                    <strong>Nome:</strong> {{ $patient->full_name }}
                </div>
                <div class="mb-3">
                    <strong>CPF:</strong> {{ $patient->cpf ?? 'N/A' }}
                </div>
                <div class="mb-3">
                    <strong>E-mail:</strong> {{ $patient->email ?? 'N/A' }}
                </div>
                <div class="mb-3">
                    <strong>Telefone:</strong> {{ $patient->phone ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Informações de Acesso ao Portal</h4>
                
                <div class="alert alert-info">
                    <h5><i class="mdi mdi-information-outline"></i> Credenciais de Acesso</h5>
                    
                    <div class="mb-3">
                        <strong>URL do Portal:</strong>
                        <div class="input-group mt-2">
                            <input type="text" 
                                   class="form-control" 
                                   id="portalUrl" 
                                   value="{{ route('patient.login', ['tenant' => \Spatie\Multitenancy\Models\Tenant::current()->subdomain ?? 'tenant']) }}" 
                                   readonly>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="copyToClipboard('portalUrl')"
                                    title="Copiar URL">
                                <i class="mdi mdi-content-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>E-mail:</strong>
                        <div class="input-group mt-2">
                            <input type="text" 
                                   class="form-control" 
                                   id="loginEmail" 
                                   value="{{ $patient->login->email }}" 
                                   readonly>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="copyToClipboard('loginEmail')"
                                    title="Copiar e-mail">
                                <i class="mdi mdi-content-copy"></i>
                            </button>
                        </div>
                    </div>

                    @if(session('password'))
                        <div class="mb-3">
                            <strong>Senha:</strong>
                            <div class="input-group mt-2">
                                <input type="password" 
                                       class="form-control" 
                                       id="loginPassword" 
                                       value="{{ session('password') }}" 
                                       readonly>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePasswordVisibility('loginPassword')"
                                        title="Mostrar/Ocultar senha">
                                    <i class="mdi mdi-eye" id="toggleIcon"></i>
                                </button>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="copyToClipboard('loginPassword')"
                                        title="Copiar senha">
                                    <i class="mdi mdi-content-copy"></i>
                                </button>
                            </div>
                            <small class="text-warning">
                                <i class="mdi mdi-alert"></i> Anote esta senha! Ela não será exibida novamente.
                            </small>
                        </div>
                    @else
                        <div class="mb-3">
                            <strong>Senha:</strong>
                            <div class="alert alert-warning">
                                <i class="mdi mdi-lock"></i> A senha não pode ser visualizada por questões de segurança.
                                <br>
                                <small>Para redefinir a senha, edite o login do paciente.</small>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Status:</strong>
                        @if($patient->login->is_active)
                            <span class="badge bg-success">Acesso Ativo</span>
                        @else
                            <span class="badge bg-warning">Acesso Bloqueado</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Enviar Informações de Acesso</h4>
                <p class="text-muted">Envie as credenciais de acesso ao paciente por e-mail ou WhatsApp.</p>
                
                <form id="sendEmailForm" 
                      action="{{ route('tenant.patients.login.send-email', $patient->id) }}" 
                      method="POST" 
                      class="d-inline">
                    @csrf
                    @if(session('password'))
                        <input type="hidden" name="password" value="{{ session('password') }}">
                    @endif
                    <button type="submit" 
                            class="btn btn-primary btn-lg me-2"
                            onclick="return confirm('Deseja enviar as informações de acesso por e-mail?');">
                        <i class="mdi mdi-email-send"></i> Enviar por E-mail
                    </button>
                </form>

                @if($patient->phone)
                    <form id="sendWhatsAppForm" 
                          action="{{ route('tenant.patients.login.send-whatsapp', $patient->id) }}" 
                          method="POST" 
                          class="d-inline">
                        @csrf
                        @if(session('password'))
                            <input type="hidden" name="password" value="{{ session('password') }}">
                        @endif
                        <button type="submit" 
                                class="btn btn-success btn-lg me-2"
                                onclick="return confirm('Deseja enviar as informações de acesso por WhatsApp?');">
                            <i class="mdi mdi-whatsapp"></i> Enviar por WhatsApp
                        </button>
                    </form>
                @else
                    <button type="button" 
                            class="btn btn-success btn-lg me-2" 
                            disabled
                            title="Paciente não possui telefone cadastrado">
                        <i class="mdi mdi-whatsapp"></i> Enviar por WhatsApp
                    </button>
                    <small class="text-muted d-block mt-1">Adicione um telefone ao paciente para enviar por WhatsApp.</small>
                @endif

                <a href="{{ route('tenant.patients.index') }}" class="btn btn-secondary btn-lg">
                    <i class="mdi mdi-arrow-left"></i> Voltar para Lista
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(inputId) {
        const input = document.getElementById(inputId);
        input.select();
        input.setSelectionRange(0, 99999); // Para dispositivos móveis
        
        try {
            document.execCommand('copy');
            
            // Feedback visual
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="mdi mdi-check"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        } catch (err) {
            alert('Erro ao copiar. Por favor, copie manualmente.');
        }
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById('toggleIcon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('mdi-eye');
            icon.classList.add('mdi-eye-off');
        } else {
            input.type = 'password';
            icon.classList.remove('mdi-eye-off');
            icon.classList.add('mdi-eye');
        }
    }
</script>
@endpush

