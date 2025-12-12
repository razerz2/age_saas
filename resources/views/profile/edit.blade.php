@extends('layouts.freedash.app')

@section('content')

    <div class="container-fluid">
        
        <!-- Título e breadcrumb -->
        <div class="page-breadcrumb">
            <div class="row">
                <div class="col-7 align-self-center">
                    <h4 class="page-title text-dark font-weight-medium mb-1">Perfil do Usuário</h4>
                    <div class="d-flex align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0">
                                <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                        class="text-muted">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item text-muted active" aria-current="page">Perfil</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulários -->
        <div class="row">
            <div class="col-lg-8 col-md-10">

                {{-- Atualizar informações do perfil --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Informações Pessoais</h4>
                        <p class="card-subtitle mb-4">
                            Atualize seu nome, e-mail e outras informações básicas da conta.
                        </p>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Atualizar senha --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Atualizar Senha</h4>
                        <p class="card-subtitle mb-4">
                            Mantenha sua conta segura alterando sua senha regularmente.
                        </p>
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- Autenticação de dois fatores --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Autenticação de Dois Fatores</h4>
                        <p class="card-subtitle mb-4">
                            Adicione uma camada extra de segurança à sua conta com autenticação de dois fatores.
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($user->hasTwoFactorEnabled())
                                    <span class="badge bg-success">
                                        <i class="mdi mdi-shield-check me-1"></i>
                                        2FA Ativado
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="mdi mdi-shield-alert me-1"></i>
                                        2FA Desativado
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('Platform.two-factor.index') }}" class="btn btn-outline-primary">
                                <i class="mdi mdi-shield-account me-1"></i>
                                Configurar 2FA
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Excluir conta --}}
                <div class="card border-danger">
                    <div class="card-body">
                        <h4 class="card-title text-danger">Excluir Conta</h4>
                        <p class="card-subtitle mb-4 text-muted">
                            A exclusão da conta é permanente. Todos os seus dados serão removidos.
                        </p>
                        @include('profile.partials.delete-user-form')
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
        document.getElementById('update_password_password').value = password;
        document.getElementById('update_password_password_confirmation').value = password;
        
        // Mostra temporariamente
        document.getElementById('update_password_password').type = 'text';
        document.getElementById('update_password_password_confirmation').type = 'text';
        document.getElementById('update_password_password-eye-icon').classList.remove('fa-eye');
        document.getElementById('update_password_password-eye-icon').classList.add('fa-eye-slash');
        document.getElementById('update_password_password_confirmation-eye-icon').classList.remove('fa-eye');
        document.getElementById('update_password_password_confirmation-eye-icon').classList.add('fa-eye-slash');
        document.getElementById('update_password_password').select();
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            document.getElementById('update_password_password').type = 'password';
            document.getElementById('update_password_password_confirmation').type = 'password';
            document.getElementById('update_password_password-eye-icon').classList.remove('fa-eye-slash');
            document.getElementById('update_password_password-eye-icon').classList.add('fa-eye');
            document.getElementById('update_password_password_confirmation-eye-icon').classList.remove('fa-eye-slash');
            document.getElementById('update_password_password_confirmation-eye-icon').classList.add('fa-eye');
        }, 3000);
    }
</script>
@endpush
@endsection
