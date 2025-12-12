<!DOCTYPE html>
<html lang="pt-BR" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DoctorHub') }} - Verificação 2FA</title>

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('freedash/assets/images/favicon.png') }}">
    <link href="{{ asset('freedash/dist/css/style.min.css') }}" rel="stylesheet">

    <style>
        body {
            background-color: #f5f8fb;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-box {
            display: flex;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .login-form {
            flex: 1;
            padding: 3rem;
        }

        .login-form h2 {
            font-weight: 700;
            color: #1e3a8a;
        }

        .login-form p {
            color: #6b7280;
        }

        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .code-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-weight: 600;
        }

        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-form">
                <div class="text-center mb-4">
                    <img src="{{ asset('freedash/assets/images/big/icon.png') }}" alt="logo" width="70">
                </div>

                <h2 class="text-center mb-1">Verificação de Dois Fatores</h2>
                @if(isset($method) && in_array($method, ['email', 'whatsapp']))
                    <p class="text-center mb-4">Digite o código de 6 dígitos enviado via {{ $method === 'email' ? 'e-mail' : 'WhatsApp' }}.</p>
                    <div class="alert alert-info text-center">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Código enviado! Verifique seu {{ $method === 'email' ? 'e-mail' : 'WhatsApp' }}.
                    </div>
                @else
                    <p class="text-center mb-4">Digite o código de 6 dígitos do seu aplicativo autenticador.</p>
                    <div class="info-box">
                        <small class="text-muted">
                            <i class="mdi mdi-information-outline"></i>
                            Você também pode usar um código de recuperação se não tiver acesso ao seu dispositivo autenticador.
                        </small>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('two-factor.challenge') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="code">Código de Verificação</label>
                        <input id="code" 
                               name="code" 
                               type="text" 
                               class="form-control code-input @error('code') is-invalid @enderror" 
                               placeholder="000000"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               required 
                               autofocus
                               autocomplete="one-time-code">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted mt-2">
                            Digite o código de 6 dígitos do seu aplicativo autenticador ou um código de recuperação.
                        </small>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="mdi mdi-shield-check me-1"></i>
                            Verificar e Continuar
                        </button>
                    </div>

                    @if(isset($method) && in_array($method, ['email', 'whatsapp']))
                        <div class="text-center mt-3">
                            <form method="POST" action="{{ route('two-factor.challenge.resend') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted small p-0 border-0 bg-transparent">
                                    <i class="mdi mdi-refresh me-1"></i>
                                    Reenviar código
                                </button>
                            </form>
                            <span class="text-muted mx-2">|</span>
                            <a href="{{ route('login') }}" class="text-muted small">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Voltar ao login
                            </a>
                        </div>
                    @else
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-muted small">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Voltar ao login
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('freedash/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        // Auto-submit quando 6 dígitos forem digitados
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // Opcional: auto-submit após 6 dígitos
                // this.form.submit();
            }
        });
    </script>
</body>

</html>

