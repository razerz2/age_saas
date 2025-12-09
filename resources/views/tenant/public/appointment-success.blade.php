<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>Agendamento Confirmado — {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">

    <style>
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .success-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card success-card">
                        <div class="card-body p-5">
                            
                            <div class="success-icon">
                                <i class="mdi mdi-check-circle"></i>
                            </div>

                            <h2 class="mb-3">Agendamento Confirmado!</h2>
                            
                            @if (session('success'))
                                <p class="text-muted mb-4">{{ session('success') }}</p>
                            @else
                                <p class="text-muted mb-4">Seu agendamento foi realizado com sucesso.</p>
                            @endif

                            <div class="alert alert-info" role="alert">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Você receberá uma confirmação por e-mail em breve.
                            </div>

                            <div class="mt-4 d-flex justify-content-center gap-3">
                                @if(isset($appointment_id) && $appointment_id)
                                    <a href="{{ route('public.appointment.show', ['slug' => $tenant->subdomain, 'appointment_id' => $appointment_id]) }}" class="btn btn-outline-primary btn-lg">
                                        <i class="mdi mdi-eye me-2"></i>
                                        Ver Agendamento
                                    </a>
                                @endif
                                <a href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}" class="btn btn-primary btn-lg">
                                    <i class="mdi mdi-calendar-plus me-2"></i>
                                    Fazer Novo Agendamento
                                </a>
                            </div>

                            <div class="mt-3">
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

</body>

</html>

