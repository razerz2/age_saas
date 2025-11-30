<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Formulário Enviado — {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}</title>
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
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
            padding: 3rem;
            text-align: center;
        }
        .success-icon {
            font-size: 64px;
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
                    <div class="success-card">
                        <i class="mdi mdi-check-circle success-icon"></i>
                        <h2 class="mb-3">Formulário Enviado com Sucesso!</h2>
                        <p class="text-muted mb-4">
                            Sua resposta foi registrada. Obrigado por preencher o formulário.
                        </p>
                        @if($response->appointment)
                            <a href="{{ tenant_route($tenant, 'public.appointment.show', ['appointment_id' => $response->appointment->id]) }}" class="btn btn-primary">
                                <i class="mdi mdi-calendar-clock me-2"></i>
                                Ver Agendamento
                            </a>
                        @endif
                        <div class="mt-4">
                            <a href="{{ tenant_route($tenant, 'public.patient.identify') }}" class="btn btn-light">
                                <i class="mdi mdi-home me-2"></i>
                                Voltar ao Início
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

