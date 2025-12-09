<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Login — Sistema</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">

                            {{-- LOGO --}}
                            <div class="brand-logo text-center mb-4">
                                <img src="{{ asset('connect_plus/assets/images/logo-dark.svg') }}" alt="Logo">
                            </div>

                            <h4>Bem-vindo! <p>Tenant: {{ $tenant->subdomain ?? 'NULO' }}</p>
                            </h4>
                            <h6 class="font-weight-light mb-4">Entre para continuar</h6>

                            {{-- FORM LOGIN --}}
                            <form method="POST"
                                action="{{ route('tenant.login.submit', ['slug' => $tenant->subdomain]) }}"
                                class="pt-3">
                                @csrf

                                {{-- EMAIL --}}
                                <div class="form-group">
                                    <input type="email" name="email"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        placeholder="E-mail" value="{{ old('email') }}" required autofocus>
                                    @error('email')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- SENHA --}}
                                <div class="form-group">
                                    <input type="password" name="password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        placeholder="Senha" required>
                                    @error('password')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- BOTÃO LOGIN --}}
                                <div class="mt-3">
                                    <button type="submit"
                                        class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                                        Entrar
                                    </button>
                                </div>

                                {{-- MANTER CONECTADO + ESQUECEU A SENHA --}}
                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" name="remember" class="form-check-input">
                                            Manter conectado
                                        </label>
                                    </div>

                                    @if (Route::has('password.request'))
                                        <a href="#" class="auth-link text-black">
                                            Esqueceu a senha?
                                        </a>
                                    @endif
                                </div>

                                {{-- CRIAR CONTA --}}
                                @if (Route::has('register'))
                                    <div class="text-center mt-4 font-weight-light">
                                        Não tem uma conta?
                                        <a href="#" class="text-primary">Criar</a>
                                    </div>
                                @endif
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="{{ asset('connect_plus/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/misc.js') }}"></script>

</body>

</html>
