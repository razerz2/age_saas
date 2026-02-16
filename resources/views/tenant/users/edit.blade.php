@extends('layouts.tailadmin.app')

@section('title', 'Editar Usuário')

@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Usuário</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.users.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Usuários</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <x-help-button module="users" />
        </div>
    </div>

    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-900">Editar Usuário</h2>
                    <p class="text-sm text-gray-500">Atualize as informações do usuário abaixo</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ workspace_route('tenant.users.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Seção: Dados Pessoais -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Dados Pessoais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name_full" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name_full" id="name_full"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name_full') border-red-300 @enderror"
                                value="{{ old('name_full', $user->name_full) }}" placeholder="Digite o nome completo" required>
                            @error('name_full')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome de Exibição <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name') border-red-300 @enderror"
                                value="{{ old('name', $user->name) }}" placeholder="Digite o nome de exibição" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto de Perfil
                        </label>
                        <div class="flex gap-3 mb-2">
                            <input type="file" name="avatar" id="avatar-input" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('avatar') border-red-300 @enderror"
                                accept="image/*">
                            <button type="button" id="webcam-btn" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Webcam
                            </button>
                        </div>
                        <p class="text-sm text-gray-500">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Foto atual -->
                        @if ($user->avatar)
                            <div class="mt-3 flex items-center gap-4">
                                <div>
                                    <img src="{{ Storage::url($user->avatar) }}" alt="Foto atual" 
                                         class="w-20 h-20 rounded-full border-4 border-gray-200 object-cover">
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700 font-medium">Foto atual</p>
                                    <p class="text-sm text-gray-500">Deixe em branco para manter a foto atual</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Seção: Contato e Acesso -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Contato e Acesso
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                                Telefone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="telefone" id="telefone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('telefone') border-red-300 @enderror"
                                value="{{ old('telefone', $user->telefone) }}" placeholder="(00) 00000-0000" required>
                            @error('telefone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                E-mail
                            </label>
                            <input type="email" name="email" id="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('email') border-red-300 @enderror"
                                value="{{ old('email', $user->email) }}" placeholder="exemplo@email.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Configurações -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.37a1.724 1.724 0 00-2.572-1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31.826-2.37.826a1.724 1.724 0 00-1.065-2.572C4.93 8.268 4.93 7.09 4.93 5.74a1.724 1.724 0 001.066-2.573c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31-.826 2.37-.826a1.724 1.724 0 001.065 2.572c1.756-.426 1.756-2.924 0-3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.37a1.724 1.724 0 00-2.572-1.065c-.426 1.756-2.924 1.756-3.35 0z"></path>
                        </svg>
                        Configurações
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                Perfil <span class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role-select" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('role') border-red-300 @enderror" required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Usuário Comum</option>
                                <option value="doctor" {{ old('role', $user->role) == 'doctor' ? 'selected' : '' }}>Usuário Médico</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('status') border-red-300 @enderror" required>
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                                <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.users.index') }}" class="btn-patient-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5m-4-4v5h18a2 2 0 002-2v-5a2 2 0 00-2-2h-5z"></path>
                        </svg>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-users.css') }}" rel="stylesheet">
    <style>
        .btn-patient-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: #2563eb;
            color: white;
        }
        
        .btn-patient-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .btn-patient-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: transparent;
            color: #374151;
            text-decoration: none;
        }
        
        .btn-patient-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }
        
        /* Dark mode styles */
        @media (prefers-color-scheme: dark) {
            .btn-patient-primary {
                background-color: transparent;
                border-color: #d1d5db;
                color: white;
            }
            
            .btn-patient-primary:hover {
                background-color: #1f2937;
                border-color: #9ca3af;
            }
            
            .btn-patient-secondary {
                background-color: transparent;
                border-color: #d1d5db;
                color: white;
            }
            
            .btn-patient-secondary:hover {
                background-color: #1f2937;
                border-color: #9ca3af;
            }
        }
        
        /* For TailAdmin dark mode class */
        .dark .btn-patient-primary {
            background-color: transparent;
            border-color: #d1d5db;
            color: white;
        }
        
        .dark .btn-patient-primary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
        }
        
        .dark .btn-patient-secondary {
            background-color: transparent;
            border-color: #d1d5db;
            color: white;
        }
        
        .dark .btn-patient-secondary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
        }
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreviewContainer = document.getElementById('avatar-preview-container');
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarFilename = document.getElementById('avatar-filename');
        const avatarRemove = document.getElementById('avatar-remove');
        const originalAvatar = '{{ $user->avatar ? asset("storage/" . $user->avatar) : asset("tailadmin/assets/images/user/user-01.jpg") }}';
        const hasOriginalAvatar = {{ $user->avatar ? 'true' : 'false' }};
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
                showAlert({ type: 'warning', title: 'Atenção', message: 'Por favor, selecione um arquivo de imagem válido.' });
                avatarInput.value = '';
            }
        }

        // Event listener para mudança no input
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamanho (2MB)
                if (file.size > 2048 * 1024) {
                    showAlert({ type: 'warning', title: 'Atenção', message: 'O arquivo é muito grande. Por favor, selecione uma imagem com no máximo 2MB.' });
                    avatarInput.value = '';
                    if (!hasOriginalAvatar) {
                        avatarPreviewContainer.style.display = 'none';
                    }
                    return;
                }
                showPreview(file);
            }
        });

        // Botão para remover imagem
        avatarRemove.addEventListener('click', function() {
            avatarInput.value = '';
            avatarPreview.src = originalAvatar;
            avatarFilename.textContent = hasOriginalAvatar ? 'Imagem atual do usuário' : 'Nenhuma imagem selecionada';
            if (!hasOriginalAvatar) {
                avatarPreviewContainer.style.display = 'none';
            }
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
                showAlert({ type: 'error', title: 'Erro', message: 'Erro ao acessar a webcam: ' + err.message });
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
            const isDoctorSection = document.getElementById('is-doctor-section');
            
            // Campo "É Médico?" - só aparece quando role é "admin"
            if (isDoctorSection) {
                if (role === 'admin') {
                    isDoctorSection.style.display = 'block';
                } else {
                    isDoctorSection.style.display = 'none';
                    // Resetar valor quando ocultar (exceto se já estava como doctor)
                    if (isDoctorSelect && role !== 'doctor') {
                        isDoctorSelect.value = '0';
                    }
                }
            }
            
            // Ajustar campo "é médico" automaticamente quando role é doctor
            if (isDoctorSelect && role === 'doctor') {
                isDoctorSelect.value = '1'; // Marca como "Sim"
            }
            
            // Controlar exibição de "Médicos Permitidos"
            // Aparece se: role selecionado é "user" E usuário logado não é médico
            // NÃO aparece se role é "admin"
            if (doctorPermissionsSection) {
                if (role === 'user' && loggedUserRole !== 'doctor') {
                    doctorPermissionsSection.style.display = 'block';
                } else {
                    doctorPermissionsSection.style.display = 'none';
                }
            }
            
            // Controlar exibição e pré-seleção de "Módulos"
            // NÃO aparece se role é "admin"
            if (modulesSection) {
                if (role === 'admin') {
                    modulesSection.style.display = 'none';
                } else {
                    modulesSection.style.display = 'block';
                    
                    // Atualizar mensagem informativa
                    const modulesInfoText = document.getElementById('modules-info-text');
                    if (modulesInfoText) {
                        if (role === 'doctor') {
                            modulesInfoText.innerHTML = '<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para médicos em <a href="{{ workspace_route("tenant.settings.index") }}" target="_blank">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.';
                        } else {
                            modulesInfoText.innerHTML = '<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para usuários comuns em <a href="{{ workspace_route("tenant.settings.index") }}" target="_blank">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.';
                        }
                    }
                    
                    // Pré-selecionar módulos padrão baseado no role
                    updateModulesSelection(role);
                }
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
            
            // Só atualizar se não houver módulos já selecionados (para não sobrescrever dados existentes)
            // Mas se o usuário mudar o role, podemos atualizar
            const hasCheckedModules = Array.from(moduleCheckboxes).some(cb => cb.checked);
            
            // Se não houver módulos marcados OU se o role mudou, aplicar os padrões
            if (!hasCheckedModules || role !== '{{ old('role', $user->role ?? 'user') }}') {
                moduleCheckboxes.forEach(checkbox => {
                    const moduleKey = checkbox.getAttribute('data-module-key');
                    checkbox.checked = defaultModules.includes(moduleKey);
                });
            }
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
