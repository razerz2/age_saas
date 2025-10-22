<!DOCTYPE html>
<html lang="pt-BR" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DoctorHub') }} - Login</title>

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
            max-width: 900px;
            width: 100%;
        }

        .login-image {
            background: url('{{ asset('freedash/assets/images/big/3.jpg') }}') no-repeat center center;
            background-size: cover;
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

        .text-link {
            color: #2563eb;
            text-decoration: none;
        }

        .text-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Lado esquerdo - imagem -->
            <div class="login-image"
                style="background:url('{{ asset('freedash/assets/images/big/3.jpg') }}') no-repeat center center; background-size: cover;">
            </div>

            <!-- Lado direito - formulário -->
            <div class="login-form">
                <div class="text-center mb-4">
                    <img src="{{ asset('freedash/assets/images/big/icon.png') }}" alt="logo" width="70">
                </div>

                <h2 class="text-center mb-1">Acessar DoctorHub</h2>
                <p class="text-center mb-4">Digite seu e-mail e senha para entrar na sua conta.</p>

                @if (session('status'))
                    <div class="alert alert-success small text-center">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="email">E-mail</label>
                        <input id="email" name="email" type="email"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Digite seu e-mail"
                            value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">Senha</label>
                        <input id="password" name="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Digite sua senha"
                            required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember -->
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label small text-muted" for="remember_me">
                            Lembrar-me
                        </label>
                    </div>

                    <!-- Forgot -->
                    @if (Route::has('password.request'))
                        <div class="text-end mb-3">
                            <a class="text-link small" href="{{ route('password.request') }}">
                                Esqueceu sua senha?
                            </a>
                        </div>
                    @endif

                    <!-- Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Entrar</button>
                    </div>

                    <!-- Register -->
                    @if (Route::has('register'))
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0 small">
                                Não tem uma conta?
                                <a href="{{ route('register') }}" class="text-link fw-semibold">
                                    Registre-se
                                </a>
                            </p>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('freedash/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
