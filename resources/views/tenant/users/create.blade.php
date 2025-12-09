@extends('layouts.connect_plus.app')

@section('title', 'Criar Usuário')

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0"> Criar Usuário </h3>
            <x-help-button module="users" />
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.users.index') }}">Usuários</a>
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
                                <i class="mdi mdi-account-plus text-primary me-2"></i>
                                Novo Usuário
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para cadastrar um novo usuário</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.users.store') }}" class="forms-sample" enctype="multipart/form-data">
                        @csrf

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
                                               value="{{ old('name_full') }}" placeholder="Digite o nome completo" required>
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
                                               value="{{ old('name') }}" placeholder="Digite o nome de exibição" required>
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

                        {{-- Seção: Contato e Acesso --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-phone me-2"></i>
                                Contato e Acesso
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-phone me-1"></i>
                                            Telefone <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="telefone" class="form-control @error('telefone') is-invalid @enderror" 
                                               value="{{ old('telefone') }}" placeholder="(00) 00000-0000" required>
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
                                               value="{{ old('email') }}" placeholder="exemplo@email.com">
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-lock me-1"></i>
                                            Senha
                                        </label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                               placeholder="Digite a senha">
                                        <small class="form-text text-muted">Deixe em branco para gerar senha automática</small>
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
                                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               placeholder="Confirme a senha">
                                        <small class="form-text text-muted">Digite a senha novamente para confirmar</small>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Configurações --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-cog-outline me-2"></i>
                                Configurações
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account-key me-1"></i>
                                            Perfil <span class="text-danger">*</span>
                                        </label>
                                        <select name="role" id="role-select" class="form-control @error('role') is-invalid @enderror" required>
                                            <option value="user" {{ old('role', 'user') == 'user' ? 'selected' : '' }}>Usuário Comum</option>
                                            <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Usuário Médico</option>
                                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            É Médico?
                                        </label>
                                        <select name="is_doctor" id="is-doctor-select" class="form-control @error('is_doctor') is-invalid @enderror">
                                            <option value="0" {{ old('is_doctor', '0') == '0' ? 'selected' : '' }}>Não</option>
                                            <option value="1" {{ old('is_doctor') == '1' ? 'selected' : '' }}>Sim</option>
                                        </select>
                                        @error('is_doctor')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-toggle-switch me-1"></i>
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                                            <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Permissões de Médicos (para usuário comum) --}}
                        <div class="mb-4" id="doctor-permissions-section" style="display: none;">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-doctor me-2"></i>
                                Médicos Permitidos
                            </h5>
                            <div class="form-group">
                                <label class="fw-semibold mb-2">Selecione os médicos que este usuário pode visualizar:</label>
                                @php
                                    $doctors = \App\Models\Tenant\Doctor::with('user')->get();
                                    $oldDoctorIds = old('doctor_ids', []);
                                @endphp
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($doctors as $doctor)
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" 
                                                        class="form-check-input" 
                                                        name="doctor_ids[]"
                                                        value="{{ $doctor->id }}" 
                                                        {{ in_array($doctor->id, $oldDoctorIds) ? 'checked' : '' }}>
                                                    {{ $doctor->user->name_full ?? $doctor->user->name }}
                                                    <i class="input-helper"></i>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('doctor_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Seção: Seleção de Médico (removida - médico não representa outro médico) --}}

                        {{-- Seção: Módulos --}}
                        <div class="mb-4" id="modules-section">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-view-module me-2"></i>
                                Módulos
                            </h5>
                            <div class="form-group">
                                <label class="fw-semibold mb-2">Selecione os módulos disponíveis para este usuário:</label>
                                <div class="alert alert-info" id="modules-info-alert">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <span id="modules-info-text">
                                        <strong>Nota:</strong> Os módulos serão pré-selecionados conforme as configurações padrão em <a href="{{ route('tenant.settings.index') }}" target="_blank">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.
                                    </span>
                                </div>
                                @php
                                    $allModules = App\Models\Tenant\Module::all();
                                    // Sempre remover módulo "usuários" - apenas admins têm acesso, mas não podem atribuir a outros
                                    $modules = collect($allModules)->reject(function($module) {
                                        return $module['key'] === 'users';
                                    })->values()->all();
                                    
                                    // Carregar módulos padrão baseado no role inicial
                                    $initialRole = old('role', 'user');
                                    $defaultModules = [];
                                    if ($initialRole === 'doctor') {
                                        $defaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
                                    } elseif ($initialRole === 'user') {
                                        $defaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
                                    }
                                    
                                    // Se houver old('modules'), usar ele, senão usar os padrões
                                    $oldModules = old('modules', $defaultModules);
                                @endphp
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($modules as $module)
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" 
                                                        class="form-check-input module-checkbox" 
                                                        name="modules[]"
                                                        value="{{ $module['key'] }}" 
                                                        data-module-key="{{ $module['key'] }}"
                                                        {{ in_array($module['key'], $oldModules) ? 'checked' : '' }}>
                                                    {{ $module['name'] }}
                                                    <i class="input-helper"></i>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('modules')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.users.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Usuário
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-users.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreviewContainer = document.getElementById('avatar-preview-container');
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarFilename = document.getElementById('avatar-filename');
        const avatarRemove = document.getElementById('avatar-remove');
        const roleSelect = document.getElementById('role-select');
        const isDoctorSelect = document.getElementById('is-doctor-select');
        const doctorPermissionsSection = document.getElementById('doctor-permissions-section');
        const modulesSection = document.getElementById('modules-section');
        const loggedUserRole = '{{ Auth::guard("tenant")->user()->role ?? "" }}';

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
                avatarInput.value = '';
            }
        }

        // Event listener para mudança no input
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

        // Botão para remover imagem
        avatarRemove.addEventListener('click', function() {
            avatarInput.value = '';
            avatarPreviewContainer.style.display = 'none';
            avatarPreview.src = '';
            avatarFilename.textContent = '';
        });

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
        webcamBtn.addEventListener('click', function() {
            webcamModal.show();
        });

        // Iniciar webcam
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

        // Capturar foto
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

        // Parar webcam
        webcamStop.addEventListener('click', function() {
            stopWebcam();
        });

        function stopWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            webcamVideo.srcObject = null;
            webcamVideo.style.display = 'none';
            webcamPlaceholder.style.display = 'block';
            webcamStart.style.display = 'inline-block';
            webcamCapture.style.display = 'none';
            webcamStop.style.display = 'none';
        }

        // Parar webcam quando modal fechar
        document.getElementById('webcam-modal').addEventListener('hidden.bs.modal', function() {
            stopWebcam();
        });

        // Controlar exibição de seções baseado no role
        function toggleRoleSections() {
            const role = roleSelect.value;
            
            // Ajustar campo "é médico" automaticamente
            if (isDoctorSelect) {
                if (role === 'doctor') {
                    isDoctorSelect.value = '1'; // Marca como "Sim"
                } else if (role === 'user' || role === 'admin') {
                    isDoctorSelect.value = '0'; // Marca como "Não"
                }
            }
            
            // Controlar exibição de "Médicos Permitidos"
            // Aparece se: role selecionado é "user" E usuário logado não é médico
            // (admin pode ver e configurar para outros usuários)
            if (doctorPermissionsSection) {
                if (role === 'user' && loggedUserRole !== 'doctor') {
                    doctorPermissionsSection.style.display = 'block';
                } else {
                    doctorPermissionsSection.style.display = 'none';
                }
            }
            
            // Controlar exibição e pré-seleção de "Módulos"
            // Sempre exibir, mas pré-selecionar conforme configurações padrão
            if (modulesSection) {
                modulesSection.style.display = 'block';
                
                // Atualizar mensagem informativa
                const modulesInfoText = document.getElementById('modules-info-text');
                if (modulesInfoText) {
                    if (role === 'admin') {
                        modulesInfoText.innerHTML = '<strong>Nota:</strong> Administradores têm acesso total ao sistema. Você pode selecionar módulos específicos se necessário.';
                    } else if (role === 'doctor') {
                        modulesInfoText.innerHTML = '<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para médicos em <a href="{{ route("tenant.settings.index") }}" target="_blank">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.';
                    } else {
                        modulesInfoText.innerHTML = '<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para usuários comuns em <a href="{{ route("tenant.settings.index") }}" target="_blank">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.';
                    }
                }
                
                // Pré-selecionar módulos padrão baseado no role
                updateModulesSelection(role);
            }
        }

        // Dados dos módulos padrão carregados do servidor
        @php
            $commonUserDefaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
            $doctorDefaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
        @endphp
        
        const defaultModulesData = {
            'user': @json($commonUserDefaultModules),
            'doctor': @json($doctorDefaultModules),
            'admin': []
        };

        // Função para atualizar seleção de módulos baseado no role
        function updateModulesSelection(role) {
            const defaultModules = defaultModulesData[role] || [];
            const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
            
            moduleCheckboxes.forEach(checkbox => {
                const moduleKey = checkbox.getAttribute('data-module-key');
                checkbox.checked = defaultModules.includes(moduleKey);
            });
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleRoleSections);
            // Executar na carga inicial
            toggleRoleSections();
        } else {
            console.error('roleSelect não encontrado');
        }
    });
</script>
@endpush

@endsection
