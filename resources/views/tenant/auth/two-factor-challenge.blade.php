<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Verificação 2FA — Sistema</title>

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

                            <h4>Verificação de Dois Fatores</h4>
                            @if(isset($method) && in_array($method, ['email', 'whatsapp']))
                                <h6 class="font-weight-light mb-4">Digite o código de 6 dígitos enviado via {{ $method === 'email' ? 'e-mail' : 'WhatsApp' }}.</h6>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <strong>Código enviado!</strong> Verifique seu {{ $method === 'email' ? 'e-mail' : 'WhatsApp' }}.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @else
                                <h6 class="font-weight-light mb-4">Digite o código de 6 dígitos do seu aplicativo autenticador.</h6>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    <strong>Dica:</strong> Você também pode usar um código de recuperação se não tiver acesso ao seu dispositivo autenticador.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- FORM VERIFICAÇÃO 2FA --}}
                            <form method="POST"
                                action="{{ route('tenant.two-factor.challenge', ['slug' => $tenant->subdomain]) }}"
                                class="pt-3"
                                id="two-factor-form">
                                @csrf

                                {{-- CÓDIGO --}}
                                <div class="form-group">
                                    <label class="form-label">Código de Verificação</label>
                                    <input type="text" 
                                           name="code" 
                                           id="code"
                                           class="form-control form-control-lg @error('code') is-invalid @enderror"
                                           placeholder="000000"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           required 
                                           autofocus
                                           autocomplete="one-time-code"
                                           style="font-size: 1.5rem; text-align: center; letter-spacing: 0.5rem; font-weight: 600;">
                                    @error('code')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted mt-2">
                                        Digite o código de 6 dígitos do seu aplicativo autenticador ou um código de recuperação.
                                    </small>
                                </div>

                                {{-- BOTÃO VERIFICAR --}}
                                <div class="mt-3">
                                    <button type="submit"
                                        class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                                        <i class="mdi mdi-shield-check me-1"></i>
                                        Verificar e Continuar
                                    </button>
                                </div>

                                {{-- REENVIAR / VOLTAR AO LOGIN --}}
                                <div class="text-center mt-4 font-weight-light">
                                    @if(isset($method) && in_array($method, ['email', 'whatsapp']))
                                        <form method="POST" action="{{ route('tenant.two-factor.challenge.resend', ['slug' => $tenant->subdomain]) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-primary p-0 border-0 bg-transparent font-weight-light">
                                                <i class="mdi mdi-refresh me-1"></i>
                                                Reenviar código
                                            </button>
                                        </form>
                                        <span class="text-muted mx-2">|</span>
                                    @endif
                                    <a href="{{ route('tenant.login', ['slug' => $tenant->subdomain]) }}" class="text-primary">
                                        <i class="mdi mdi-arrow-left me-1"></i>
                                        Voltar ao login
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('connect_plus/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/misc.js') }}"></script>
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

