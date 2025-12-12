@extends('layouts.connect_plus.app')

@section('title', 'Alterar Senha')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Alterar Senha </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.users.index') }}">Usuários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Alterar Senha</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Alterar Senha</h4>
                    <p class="card-description">Digite a senha atual e a nova senha.</p>

                    <form method="POST" action="{{ workspace_route('tenant.users.change-password.store', $user->id) }}"
                        class="forms-sample">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="current_password">Senha Atual</label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('current_password')" title="Mostrar/Ocultar senha">
                                    <i class="mdi mdi-eye" id="current_password-eye-icon"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('new_password')" title="Mostrar/Ocultar senha">
                                    <i class="mdi mdi-eye" id="new_password-eye-icon"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                    <i class="mdi mdi-refresh me-1"></i> Gerar
                                </button>
                            </div>
                            <small class="form-text text-muted">Mínimo 8 caracteres com maiúscula, minúscula, número e caractere especial</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password_confirmation">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('new_password_confirmation')" title="Mostrar/Ocultar senha">
                                    <i class="mdi mdi-eye" id="new_password_confirmation-eye-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Alterar Senha</button>
                            <a href="{{ workspace_route('tenant.users.index') }}" class="btn btn-light btn-lg">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

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
        document.getElementById('new_password').value = password;
        document.getElementById('new_password_confirmation').value = password;
        
        // Mostra temporariamente
        document.getElementById('new_password').type = 'text';
        document.getElementById('new_password_confirmation').type = 'text';
        document.getElementById('new_password-eye-icon').classList.remove('mdi-eye');
        document.getElementById('new_password-eye-icon').classList.add('mdi-eye-off');
        document.getElementById('new_password_confirmation-eye-icon').classList.remove('mdi-eye');
        document.getElementById('new_password_confirmation-eye-icon').classList.add('mdi-eye-off');
        document.getElementById('new_password').select();
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            document.getElementById('new_password').type = 'password';
            document.getElementById('new_password_confirmation').type = 'password';
            document.getElementById('new_password-eye-icon').classList.remove('mdi-eye-off');
            document.getElementById('new_password-eye-icon').classList.add('mdi-eye');
            document.getElementById('new_password_confirmation-eye-icon').classList.remove('mdi-eye-off');
            document.getElementById('new_password_confirmation-eye-icon').classList.add('mdi-eye');
        }, 3000);
    }
</script>
@endpush

@endsection
