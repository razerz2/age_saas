@extends('layouts.tailadmin.public')

@section('title', 'Formulário Enviado — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')


@section('content')
    <div class="page-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="success-card success-card-padded">
                        <i class="mdi mdi-check-circle success-icon success-icon-sm"></i>
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

