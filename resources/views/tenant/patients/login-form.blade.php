@extends('layouts.connect_plus.app')

@section('title', 'Gerenciar Login do Paciente')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-key"></i>
        </span>
        Gerenciar Login do Paciente
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
            </div>
        </div>
    </div>

    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ (isset($patient->login) && $patient->login) ? 'Editar Login' : 'Criar Login' }}
                </h4>

                <form method="POST" action="{{ route('tenant.patients.login.store', $patient->id) }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">E-mail para Login *</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', (isset($patient->login) && $patient->login) ? $patient->login->email : '') }}" 
                               placeholder="Digite o e-mail para login"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Este será o e-mail usado para acesso ao portal do paciente.</small>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            Senha {{ (isset($patient->login) && $patient->login) ? '(deixe em branco para não alterar)' : '*' }}
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password"
                                   {{ !(isset($patient->login) && $patient->login) ? 'required' : '' }}
                                   minlength="6">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="generatePassword"
                                    title="Gerar senha aleatória">
                                <i class="mdi mdi-refresh"></i> Gerar
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Mínimo de 6 caracteres.</small>
                    </div>

                    @if(!isset($patient->login) || !$patient->login)
                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Senha *</label>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   required
                                   minlength="6">
                            @error('password_confirmation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                        </div>
                    @endif

                    <div class="form-check mb-3">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', (isset($patient->login) && $patient->login) ? $patient->login->is_active : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Acesso Ativo
                        </label>
                        <small class="d-block text-muted">Desmarque para bloquear o acesso ao portal.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Salvar
                        </button>
                        <a href="{{ route('tenant.patients.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Gerador de senha
        $('#generatePassword').on('click', function() {
            const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lowercase = 'abcdefghijklmnopqrstuvwxyz';
            const numbers = '0123456789';
            const symbols = '!@#$%&*';
            const allChars = uppercase + lowercase + numbers + symbols;
            
            let password = '';
            // Garantir pelo menos um de cada tipo
            password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
            password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
            password += numbers.charAt(Math.floor(Math.random() * numbers.length));
            password += symbols.charAt(Math.floor(Math.random() * symbols.length));
            
            // Completar até 12 caracteres
            for (let i = password.length; i < 12; i++) {
                password += allChars.charAt(Math.floor(Math.random() * allChars.length));
            }
            
            // Embaralhar a senha
            password = password.split('').sort(() => Math.random() - 0.5).join('');
            
            $('#password').val(password);
            $('#password_confirmation').val(password);
            
            // Mostrar senha temporariamente e mudar tipo para texto
            const wasPassword = $('#password').attr('type') === 'password';
            if (wasPassword) {
                $('#password').attr('type', 'text');
                setTimeout(() => {
                    $('#password').attr('type', 'password');
                }, 5000);
            }
        });

        // Validação de confirmação de senha
        @if(!$patient->login)
        function validatePasswordConfirmation() {
            const password = $('#password').val();
            const confirmation = $('#password_confirmation').val();
            
            if (password && confirmation) {
                if (password !== confirmation) {
                    $('#password_confirmation').addClass('is-invalid');
                    if (!$('#password_confirmation').next('.invalid-feedback').length) {
                        $('#password_confirmation').after('<div class="invalid-feedback d-block">As senhas não coincidem.</div>');
                    }
                    return false;
                } else {
                    $('#password_confirmation').removeClass('is-invalid');
                    $('#password_confirmation').next('.invalid-feedback').remove();
                    return true;
                }
            }
            return true;
        }

        $('#password').on('keyup', validatePasswordConfirmation);
        $('#password_confirmation').on('keyup', validatePasswordConfirmation);

        // Validar antes de enviar formulário
        $('form').on('submit', function(e) {
            if (!validatePasswordConfirmation()) {
                e.preventDefault();
                alert('As senhas não coincidem. Por favor, verifique.');
                return false;
            }
        });
        @endif
    });
</script>
@endpush

