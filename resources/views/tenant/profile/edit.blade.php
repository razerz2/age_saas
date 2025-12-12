@extends('layouts.connect_plus.app')

@section('title', 'Meu Perfil')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Meu Perfil </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Perfil</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Container principal para alinhar todos os elementos --}}
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            
            {{-- Primeira linha: Card de Foto e Informações do Sistema lado a lado --}}
            <div class="row mb-4 profile-info-cards">
                {{-- Card de Foto do Perfil --}}
                <div class="col-12 col-md-6 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-4 flex-grow-0">
                                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('connect_plus/assets/images/faces/default.jpg') }}" 
                                     alt="Avatar" 
                                     class="rounded-circle border border-3 border-primary" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <h4 class="mb-1">{{ $user->name_full ?? $user->name }}</h4>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            <div class="mb-3 flex-grow-0">
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Administrador</span>
                                @elseif($user->role === 'doctor')
                                    <span class="badge bg-info">Médico</span>
                                @else
                                    <span class="badge bg-secondary">Usuário</span>
                                @endif
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-warning">Bloqueado</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0 mt-auto">
                                <i class="mdi mdi-phone me-1"></i>
                                {{ $user->telefone ?? 'Não informado' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card de Informações do Sistema --}}
                <div class="col-12 col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title mb-4 flex-grow-0">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações do Sistema
                            </h6>
                            <div class="flex-grow-1 d-flex flex-column justify-content-center">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="text-muted">
                                        <i class="mdi mdi-calendar-plus me-1"></i>
                                        Membro desde:
                                    </span>
                                    <span class="fw-semibold">{{ $user->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="text-muted">
                                        <i class="mdi mdi-calendar-edit me-1"></i>
                                        Última atualização:
                                    </span>
                                    <span class="fw-semibold">{{ $user->updated_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Segunda linha: Formulário de Edição --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-account-edit text-primary me-2"></i>
                                Editar Perfil
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize suas informações pessoais</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ workspace_route('tenant.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Dados Pessoais --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-account-outline me-2"></i>
                                Dados Pessoais
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Nome Completo <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name_full" class="form-control @error('name_full') is-invalid @enderror" 
                                               value="{{ old('name_full', $user->name_full) }}" placeholder="Digite o nome completo" required>
                                        @error('name_full')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account-circle me-1"></i>
                                            Nome de Exibição <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name', $user->name) }}" placeholder="Digite o nome de exibição" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-image me-1"></i>
                                            Foto de Perfil
                                        </label>
                                        <div class="d-flex gap-2 mb-2">
                                            <input type="file" name="avatar" id="avatar-input" class="form-control @error('avatar') is-invalid @enderror" 
                                                   accept="image/*">
                                            <button type="button" id="webcam-btn" class="btn btn-outline-primary">
                                                <i class="mdi mdi-camera me-1"></i>
                                                Webcam
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</small>
                                        @error('avatar')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        
                                        {{-- Pré-visualização da imagem --}}
                                        <div id="avatar-preview-container" class="mt-3" style="display: none;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-preview-wrapper">
                                                    <img id="avatar-preview" src="" alt="Preview" 
                                                         class="rounded-circle border" 
                                                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef !important;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1 text-muted">
                                                        <i class="mdi mdi-check-circle text-success me-1"></i>
                                                        <span id="avatar-filename"></span>
                                                    </p>
                                                    <button type="button" id="avatar-remove" class="btn btn-sm btn-outline-danger">
                                                        <i class="mdi mdi-delete me-1"></i>
                                                        Remover imagem
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Modal da Webcam --}}
                                    <div class="modal fade" id="webcam-modal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="mdi mdi-camera me-2"></i>
                                                        Capturar Foto
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <video id="webcam-video" autoplay playsinline style="width: 100%; max-width: 500px; border-radius: 8px; display: none;"></video>
                                                    <canvas id="webcam-canvas" style="display: none;"></canvas>
                                                    <div id="webcam-placeholder" class="p-4">
                                                        <i class="mdi mdi-camera-outline" style="font-size: 48px; color: #ccc;"></i>
                                                        <p class="text-muted mt-2">Clique em "Iniciar Webcam" para começar</p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" id="webcam-start" class="btn btn-primary">
                                                        <i class="mdi mdi-video me-1"></i>
                                                        Iniciar Webcam
                                                    </button>
                                                    <button type="button" id="webcam-capture" class="btn btn-success" style="display: none;">
                                                        <i class="mdi mdi-camera me-1"></i>
                                                        Capturar Foto
                                                    </button>
                                                    <button type="button" id="webcam-stop" class="btn btn-secondary" style="display: none;">
                                                        <i class="mdi mdi-stop me-1"></i>
                                                        Parar
                                                    </button>
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Contato --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-phone me-2"></i>
                                Informações de Contato
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-phone me-1"></i>
                                            Telefone
                                        </label>
                                        <input type="text" name="telefone" class="form-control @error('telefone') is-invalid @enderror" 
                                               value="{{ old('telefone', $user->telefone) }}" placeholder="(00) 00000-0000">
                                        @error('telefone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-email me-1"></i>
                                            E-mail
                                        </label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                               value="{{ old('email', $user->email) }}" placeholder="exemplo@email.com">
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Segurança --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-lock me-2"></i>
                                Segurança
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-lock me-1"></i>
                                            Nova Senha
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                                   placeholder="Deixe em branco para manter a senha atual">
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password')" title="Mostrar/Ocultar senha">
                                                <i class="mdi mdi-eye" id="password-eye-icon"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                                <i class="mdi mdi-refresh me-1"></i> Gerar
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Mínimo 8 caracteres com maiúscula, minúscula, número e caractere especial</small>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-lock-check me-1"></i>
                                            Confirmar Senha
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                                   placeholder="Confirme a nova senha">
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password_confirmation')" title="Mostrar/Ocultar senha">
                                                <i class="mdi mdi-eye" id="password_confirmation-eye-icon"></i>
                                            </button>
                                        </div>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Autenticação de dois fatores --}}
                            <div class="mt-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="mdi mdi-shield-account me-2"></i>
                                            Autenticação de Dois Fatores
                                        </h6>
                                        <small class="text-muted">Adicione uma camada extra de segurança à sua conta</small>
                                    </div>
                                    <div class="text-end">
                                        @if($user->hasTwoFactorEnabled())
                                            <span class="badge bg-success mb-2 d-block">
                                                <i class="mdi mdi-shield-check me-1"></i>
                                                2FA Ativado
                                            </span>
                                        @else
                                            <span class="badge bg-warning mb-2 d-block">
                                                <i class="mdi mdi-shield-alert me-1"></i>
                                                2FA Desativado
                                            </span>
                                        @endif
                                        <a href="{{ workspace_route('tenant.two-factor.index') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-cog me-1"></i>
                                            Configurar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-end align-items-center pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <style>
        /* Garantir que os cards da primeira linha tenham a mesma altura */
        .profile-info-cards .card {
            display: flex;
            flex-direction: column;
        }
        .profile-info-cards .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        /* Garantir alinhamento perfeito entre os cards superiores e o formulário */
        .profile-info-cards {
            margin-left: 0;
            margin-right: 0;
        }
        .profile-info-cards > [class*="col-"] {
            padding-left: calc(var(--bs-gutter-x) * 0.5);
            padding-right: calc(var(--bs-gutter-x) * 0.5);
        }
        /* Garantir que o formulário tenha o mesmo padding lateral */
        .col-lg-10 > .card {
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
<script src="{{ asset('js/password-generator.js') }}"></script>
<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-eye-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('mdi-eye');
            icon.classList.add('mdi-eye-off');
        } else {
            field.type = 'password';
            icon.classList.remove('mdi-eye-off');
            icon.classList.add('mdi-eye');
        }
    }
    
    function generatePassword() {
        const password = generateStrongPassword();
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Mostra temporariamente
        document.getElementById('password').type = 'text';
        document.getElementById('password_confirmation').type = 'text';
        document.getElementById('password-eye-icon').classList.remove('mdi-eye');
        document.getElementById('password-eye-icon').classList.add('mdi-eye-off');
        document.getElementById('password_confirmation-eye-icon').classList.remove('mdi-eye');
        document.getElementById('password_confirmation-eye-icon').classList.add('mdi-eye-off');
        document.getElementById('password').select();
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            document.getElementById('password').type = 'password';
            document.getElementById('password_confirmation').type = 'password';
            document.getElementById('password-eye-icon').classList.remove('mdi-eye-off');
            document.getElementById('password-eye-icon').classList.add('mdi-eye');
            document.getElementById('password_confirmation-eye-icon').classList.remove('mdi-eye-off');
            document.getElementById('password_confirmation-eye-icon').classList.add('mdi-eye');
        }, 3000);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreviewContainer = document.getElementById('avatar-preview-container');
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarFilename = document.getElementById('avatar-filename');
        const avatarRemove = document.getElementById('avatar-remove');

        // Função para exibir pré-visualização
        function showPreview(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                    avatarFilename.textContent = file.name;
                    avatarPreviewContainer.style.display = 'block';
                };
                
                reader.readAsDataURL(file);
            } else {
                alert('Por favor, selecione um arquivo de imagem válido.');
                if (avatarInput) {
                    avatarInput.value = '';
                }
            }
        }

        // Event listener para mudança no input
        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validar tamanho (2MB)
                    if (file.size > 2048 * 1024) {
                        alert('O arquivo é muito grande. Por favor, selecione uma imagem com no máximo 2MB.');
                        avatarInput.value = '';
                        avatarPreviewContainer.style.display = 'none';
                        return;
                    }
                    showPreview(file);
                }
            });
        }

        // Botão para remover imagem
        if (avatarRemove) {
            avatarRemove.addEventListener('click', function() {
                avatarInput.value = '';
                avatarPreviewContainer.style.display = 'none';
                avatarPreview.src = '';
                avatarFilename.textContent = '';
            });
        }

        // Webcam functionality
        const webcamBtn = document.getElementById('webcam-btn');
        const webcamModal = new bootstrap.Modal(document.getElementById('webcam-modal'));
        const webcamVideo = document.getElementById('webcam-video');
        const webcamCanvas = document.getElementById('webcam-canvas');
        const webcamPlaceholder = document.getElementById('webcam-placeholder');
        const webcamStart = document.getElementById('webcam-start');
        const webcamCapture = document.getElementById('webcam-capture');
        const webcamStop = document.getElementById('webcam-stop');
        let stream = null;

        // Abrir modal da webcam
        if (webcamBtn) {
            webcamBtn.addEventListener('click', function() {
                webcamModal.show();
            });
        }

        // Iniciar webcam
        if (webcamStart) {
            webcamStart.addEventListener('click', async function() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            width: { ideal: 640 },
                            height: { ideal: 480 },
                            facingMode: 'user'
                        } 
                    });
                    webcamVideo.srcObject = stream;
                    webcamVideo.style.display = 'block';
                    webcamPlaceholder.style.display = 'none';
                    webcamStart.style.display = 'none';
                    webcamCapture.style.display = 'inline-block';
                    webcamStop.style.display = 'inline-block';
                } catch (err) {
                    alert('Erro ao acessar a webcam: ' + err.message);
                    console.error('Erro ao acessar webcam:', err);
                }
            });
        }

        // Capturar foto
        if (webcamCapture) {
            webcamCapture.addEventListener('click', function() {
                const context = webcamCanvas.getContext('2d');
                webcamCanvas.width = webcamVideo.videoWidth;
                webcamCanvas.height = webcamVideo.videoHeight;
                context.drawImage(webcamVideo, 0, 0);
                
                // Converter canvas para blob
                webcamCanvas.toBlob(function(blob) {
                    if (blob) {
                        // Criar arquivo a partir do blob
                        const file = new File([blob], 'webcam-photo.jpg', { type: 'image/jpeg' });
                        
                        // Criar DataTransfer para adicionar ao input
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        avatarInput.files = dataTransfer.files;
                        
                        // Mostrar preview
                        showPreview(file);
                        
                        // Parar webcam e fechar modal
                        stopWebcam();
                        webcamModal.hide();
                    }
                }, 'image/jpeg', 0.9);
            });
        }

        // Parar webcam
        if (webcamStop) {
            webcamStop.addEventListener('click', function() {
                stopWebcam();
            });
        }

        function stopWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            if (webcamVideo) {
                webcamVideo.srcObject = null;
                webcamVideo.style.display = 'none';
            }
            if (webcamPlaceholder) {
                webcamPlaceholder.style.display = 'block';
            }
            if (webcamStart) {
                webcamStart.style.display = 'inline-block';
            }
            if (webcamCapture) {
                webcamCapture.style.display = 'none';
            }
            if (webcamStop) {
                webcamStop.style.display = 'none';
            }
        }

        // Parar webcam quando modal fechar
        const webcamModalElement = document.getElementById('webcam-modal');
        if (webcamModalElement) {
            webcamModalElement.addEventListener('hidden.bs.modal', function() {
                stopWebcam();
            });
        }

    });
</script>
@endpush

@endsection

