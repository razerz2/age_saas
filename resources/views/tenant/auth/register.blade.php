<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Registrar — Sistema</title>

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

                            <h4>Crie sua conta</h4>
                            <h6 class="font-weight-light">Leva apenas alguns segundos.</h6>

                            {{-- FORM DE REGISTRO --}}
                            <form method="POST" action="{{ route('register') }}" class="pt-3">
                                @csrf

                                {{-- NAME --}}
                                <div class="form-group">
                                    <input type="text" name="name"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        placeholder="Nome completo" value="{{ old('name') }}" required autofocus>

                                    @error('name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- EMAIL --}}
                                <div class="form-group">
                                    <input type="email" name="email"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        placeholder="E-mail" value="{{ old('email') }}" required>

                                    @error('email')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- PASSWORD --}}
                                <div class="form-group">
                                    <input type="password" name="password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        placeholder="Senha" required>

                                    @error('password')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- PASSWORD CONFIRM --}}
                                <div class="form-group">
                                    <input type="password" name="password_confirmation"
                                        class="form-control form-control-lg" placeholder="Confirme a senha" required>
                                </div>

                                {{-- TERMOS --}}
                                <div class="mb-4">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input" required>
                                            Eu concordo com os Termos & Condições
                                        </label>
                                    </div>
                                </div>

                                {{-- BOTÃO REGISTRAR --}}
                                <div class="mt-3">
                                    <button type="submit"
                                        class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                                        Registrar
                                    </button>
                                </div>

                                {{-- LOGIN --}}
                                <div class="text-center mt-4 font-weight-light">
                                    Já possui conta?
                                    <a href="{{ route('login') }}" class="text-primary">Entrar</a>
                                </div>
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
