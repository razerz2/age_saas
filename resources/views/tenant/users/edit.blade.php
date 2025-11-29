@extends('layouts.connect_plus.app')

@section('title', 'Editar Usuário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Usuário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.users.index') }}">Usuários</a>
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
                                <i class="mdi mdi-account-edit text-primary me-2"></i>
                                Editar Usuário
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações do usuário abaixo</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.users.update', $user->id) }}" class="forms-sample" enctype="multipart/form-data">
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
                                        <input type="file" name="avatar" id="avatar-input" class="form-control @error('avatar') is-invalid @enderror" 
                                               accept="image/*">
                                        <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</small>
                                        @error('avatar')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        
                                        {{-- Pré-visualização da imagem --}}
                                        <div id="avatar-preview-container" class="mt-3" style="{{ old('avatar') || $user->avatar ? '' : 'display: none;' }}">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-preview-wrapper">
                                                    <img id="avatar-preview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('connect_plus/assets/images/faces/face28.png') }}" alt="Preview" 
                                                         class="rounded-circle border" 
                                                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef !important;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1 text-muted">
                                                        <i class="mdi mdi-check-circle text-success me-1"></i>
                                                        <span id="avatar-filename">{{ $user->avatar ? 'Imagem atual do usuário' : 'Nenhuma imagem selecionada' }}</span>
                                                    </p>
                                                    <button type="button" id="avatar-remove" class="btn btn-sm btn-outline-danger">
                                                        <i class="mdi mdi-delete me-1"></i>
                                                        Remover imagem
                                                    </button>
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

                        {{-- Seção: Configurações --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-cog-outline me-2"></i>
                                Configurações
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            É Médico?
                                        </label>
                                        <select name="is_doctor" class="form-control @error('is_doctor') is-invalid @enderror">
                                            <option value="0" {{ old('is_doctor', $user->is_doctor) == 0 ? 'selected' : '' }}>Não</option>
                                            <option value="1" {{ old('is_doctor', $user->is_doctor) == 1 ? 'selected' : '' }}>Sim</option>
                                        </select>
                                        @error('is_doctor')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-toggle-switch me-1"></i>
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Ativo</option>
                                            <option value="blocked" {{ old('status', $user->status) === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Módulos --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-view-module me-2"></i>
                                Módulos
                            </h5>
                            <div class="form-group">
                                <label class="fw-semibold mb-2">Selecione os módulos disponíveis para este usuário:</label>
                                @php
                                    $modules = App\Models\Tenant\Module::all();
                                    $userModules = is_array($user->modules) ? $user->modules : (json_decode($user->modules, true) ?: []);
                                @endphp
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($modules as $module)
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" 
                                                        class="form-check-input" 
                                                        name="modules[]"
                                                        value="{{ $module['key'] }}" 
                                                        {{ in_array($module['key'], $userModules) ? 'checked' : '' }}>
                                                    {{ $module['name'] }}
                                                    <i class="input-helper"></i>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
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
                                Atualizar Usuário
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .card-title {
        font-weight: 600;
    }
    h5.text-primary {
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
    #avatar-preview-container {
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
    }
    .avatar-preview-wrapper {
        flex-shrink: 0;
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
        const originalAvatar = '{{ $user->avatar ? asset("storage/" . $user->avatar) : asset("connect_plus/assets/images/faces/face28.png") }}';
        const hasOriginalAvatar = {{ $user->avatar ? 'true' : 'false' }};

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
    });
</script>
@endpush

@endsection
