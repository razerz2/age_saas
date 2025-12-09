<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Cadastro de Paciente — {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">

    <style>
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .card-title {
            font-weight: 600;
        }
        h5.text-primary {
            font-weight: 600;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .btn-lg {
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <h4 class="card-title mb-1">
                                            <i class="mdi mdi-account-plus text-primary me-2"></i>
                                            Novo Cadastro
                                        </h4>
                                        <p class="card-description mb-0 text-muted">Preencha os dados abaixo para se cadastrar na clínica</p>
                                    </div>
                                </div>

                                {{-- Mensagens --}}
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="mdi mdi-check-circle me-2"></i>
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <form class="forms-sample" action="{{ route('public.patient.register.submit', ['slug' => $tenant->subdomain]) }}" method="POST">
                                    @csrf

                                    {{-- Seção: Dados Pessoais --}}
                                    <div class="mb-4">
                                        <h5 class="mb-3 text-primary">
                                            <i class="mdi mdi-account-outline me-2"></i>
                                            Dados Pessoais
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-account me-1"></i>
                                                        Nome Completo <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                                           name="full_name" value="{{ old('full_name') }}" 
                                                           placeholder="Digite seu nome completo" required>
                                                    @error('full_name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-card-account-details me-1"></i>
                                                        CPF <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                                           name="cpf" id="cpf" value="{{ old('cpf') }}" 
                                                           maxlength="14" placeholder="000.000.000-00" required>
                                                    @error('cpf')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-calendar me-1"></i>
                                                        Data de Nascimento
                                                    </label>
                                                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                                           name="birth_date" value="{{ old('birth_date') }}"
                                                           max="{{ date('Y-m-d') }}">
                                                    @error('birth_date')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Seção: Contato --}}
                                    <div class="mb-4">
                                        <h5 class="mb-3 text-primary">
                                            <i class="mdi mdi-phone me-2"></i>
                                            Informações de Contato
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-email me-1"></i>
                                                        E-mail
                                                    </label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                           name="email" value="{{ old('email') }}" 
                                                           placeholder="exemplo@email.com">
                                                    @error('email')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-phone me-1"></i>
                                                        Telefone
                                                    </label>
                                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                           name="phone" id="phone" value="{{ old('phone') }}" 
                                                           maxlength="20" placeholder="(00) 00000-0000">
                                                    @error('phone')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Botões de Ação --}}
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                        <a href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}" class="btn btn-light">
                                            <i class="mdi mdi-arrow-left me-1"></i>
                                            Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="mdi mdi-content-save me-1"></i>
                                            Cadastrar
                                        </button>
                                    </div>
                                </form>

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara para CPF
        const cpfInput = document.getElementById('cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                }
            });
        }

        // Máscara para Telefone
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    if (value.length <= 10) {
                        // Telefone fixo (10 dígitos)
                        value = value.replace(/(\d{2})(\d)/, '($1) $2');
                        value = value.replace(/(\d{4})(\d)/, '$1-$2');
                    } else {
                        // Celular (11 dígitos)
                        value = value.replace(/(\d{2})(\d)/, '($1) $2');
                        value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    }
                    e.target.value = value;
                }
            });
        }
    });
    </script>

</body>

</html>
