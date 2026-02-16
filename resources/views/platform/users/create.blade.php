@extends('layouts.freedash.app')
@section('title', 'Cadastrar Usuários')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    {{ isset($user) ? 'Editar Usuário' : 'Novo Usuário' }}
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.users.index') }}"
                                    class="text-muted">Usuários</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">
                                {{ isset($user) ? 'Editar' : 'Novo' }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.users.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                         {{-- 🔹 Exibição de erros de validação --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                <strong>Ops!</strong> Verifique os erros abaixo:
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fechar"></button>
                            </div>
                        @endif
                        
                        <form method="POST"
                            action="{{ isset($user) ? route('Platform.users.update', $user->id) : route('Platform.users.store') }}">
                            @csrf
                            @if (isset($user))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apelido</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $user->name ?? '') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome Completo:</label>
                                    <input type="text" name="name_full" class="form-control"
                                        value="{{ old('name_full', $user->name_full ?? '') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $user->email ?? '') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status:</label>
                                    <input type="text" name="status" class="form-control"
                                        value="active" required readonly>   
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Senha {{ isset($user) ? '(opcional)' : '' }}</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password')" title="Mostrar/Ocultar senha">
                                            <i class="fa fa-eye" id="password-eye-icon"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                            <i class="fa fa-refresh me-1"></i> Gerar
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Mínimo 8 caracteres com maiúscula, minúscula, número e caractere especial</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirmar Senha</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password_confirmation')" title="Mostrar/Ocultar senha">
                                            <i class="fa fa-eye" id="password_confirmation-eye-icon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                            </div>

                            {{-- 🔹 Seleção de Módulos --}}
                            <div class="border-top pt-3 mt-4">
                                <h5 class="mb-3 fw-semibold text-dark">
                                    <i class="fa fa-layer-group text-primary me-2"></i>
                                    Módulos Permitidos
                                </h5>
                                <div class="row">
                                    @php($modules = \App\Models\Platform\Module::all())

                                    @foreach ($modules as $module)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="modules[]"
                                                    value="{{ $module['key'] }}"
                                                    {{ in_array($module['key'], old('modules', $user->modules ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    <i
                                                        class="fa {{ $module['icon'] ?? 'fa-circle' }} text-primary me-1"></i>
                                                    {{ $module['name'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fa fa-save me-1"></i>
                                    {{ isset($user) ? 'Salvar Alterações' : 'Criar Usuário' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')

@push('scripts')
<script src="{{ asset('js/password-generator.js') }}"></script>
<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-eye-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    function generatePassword() {
        const password = generateStrongPassword();
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Mostra temporariamente
        document.getElementById('password').type = 'text';
        document.getElementById('password_confirmation').type = 'text';
        document.getElementById('password_confirmation-eye-icon').classList.remove('fa-eye');
        document.getElementById('password_confirmation-eye-icon').classList.add('fa-eye-slash');
        document.getElementById('password').select();
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            document.getElementById('password').type = 'password';
            document.getElementById('password_confirmation').type = 'password';
            document.getElementById('password_confirmation-eye-icon').classList.remove('fa-eye-slash');
            document.getElementById('password_confirmation-eye-icon').classList.add('fa-eye');
        }, 3000);
    }
</script>
@endpush
@endsection
