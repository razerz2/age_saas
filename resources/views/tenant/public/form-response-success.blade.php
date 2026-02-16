@extends('layouts.tailadmin.public')

@section('title', 'Formulário Enviado — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
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
@endpush

@section('content')
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
                        <div class="mt-4 flex flex-col gap-3">
                            @if($response->appointment)
                                <x-tailadmin-button variant="primary" size="md" href="{{ tenant_route($tenant, 'public.appointment.show', ['appointment_id' => $response->appointment->id]) }}">
                                    <i class="mdi mdi-calendar-clock"></i>
                                    Ver Agendamento
                                </x-tailadmin-button>
                            @endif
                            <x-tailadmin-button variant="secondary" size="md" href="{{ tenant_route($tenant, 'public.patient.identify') }}"
                                class="bg-transparent border-gray-200 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <i class="mdi mdi-home"></i>
                                Voltar ao Início
                            </x-tailadmin-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

