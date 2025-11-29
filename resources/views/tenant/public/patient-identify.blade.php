<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Identificação — {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}</title>

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
                    <div class="col-lg-5 mx-auto">
                        <div class="auth-form-light text-left p-5">

                            {{-- LOGO --}}
                            <div class="brand-logo text-center mb-4">
                                <img src="{{ asset('connect_plus/assets/images/logo-dark.svg') }}" alt="Logo">
                            </div>

                            <h4 class="mb-1">Identificação do Paciente</h4>
                            <h6 class="font-weight-light mb-4">
                                Informe seu CPF ou E-mail para continuar com o agendamento
                            </h6>

                            {{-- Mensagem de Sucesso quando paciente encontrado --}}
                            @if (session('success') && session('patient_found'))
                                <div class="alert alert-success" role="alert">
                                    <i class="mdi mdi-check-circle me-2"></i>
                                    <strong>Paciente identificado com sucesso!</strong><br>
                                    <small>Olá, <strong>{{ session('patient_name') }}</strong>! O sistema de agendamento público será implementado em breve.</small>
                                </div>
                            @elseif (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- Mensagem de Erro Geral --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-alert-circle me-2"></i>
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}
                                    @endforeach
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- FORM IDENTIFICAÇÃO --}}
                            <form method="POST"
                                action="{{ route('public.patient.identify.submit', ['tenant' => $tenant->subdomain]) }}"
                                class="pt-3">
                                @csrf

                                {{-- IDENTIFICADOR (CPF ou Email) --}}
                                <div class="form-group">
                                    <label for="identifier" class="form-label">
                                        CPF ou E-mail
                                    </label>
                                    <input 
                                        type="text" 
                                        id="identifier"
                                        name="identifier"
                                        class="form-control form-control-lg @error('identifier') is-invalid @enderror"
                                        placeholder="000.000.000-00 ou seu@email.com" 
                                        value="{{ old('identifier') }}" 
                                        required 
                                        autofocus
                                        autocomplete="off">
                                    @error('identifier')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted mt-2">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        Informe seu CPF ou e-mail cadastrado na clínica
                                    </small>
                                </div>

                                {{-- BOTÃO SUBMIT --}}
                                <div class="mt-4">
                                    <button type="submit"
                                        class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                                        <i class="mdi mdi-arrow-right me-2"></i>
                                        Continuar
                                    </button>
                                </div>
                            </form>

                            {{-- Mensagem quando paciente não encontrado --}}
                            @if (session('patient_not_found') || ($errors->has('identifier') && old('identifier')))
                                <div class="text-center mt-4">
                                    <div class="alert alert-warning mb-3" role="alert">
                                        <i class="mdi mdi-alert-outline me-2"></i>
                                        <strong>Você ainda não possui cadastro na clínica.</strong>
                                    </div>
                                    
                                    <a href="{{ route('public.patient.register', ['tenant' => $tenant->subdomain]) }}" 
                                       class="btn btn-outline-primary btn-lg">
                                        <i class="mdi mdi-account-plus me-2"></i>
                                        Criar Cadastro
                                    </a>
                                </div>
                            @endif

                            {{-- FOOTER --}}
                            <div class="text-center mt-5">
                                <small class="text-muted">
                                    © {{ date('Y') }} {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}. Todos os direitos reservados.
                                </small>
                            </div>

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

    {{-- Máscara para CPF --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const identifierInput = document.getElementById('identifier');
            
            identifierInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Se parece com CPF (11 dígitos ou menos), aplica máscara de CPF
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                }
            });
        });
    </script>

</body>

</html>

