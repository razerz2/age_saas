@extends('layouts.tailadmin.public')

@section('title', 'Agendamento Confirmado — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')


@section('content')
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

                            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                                @if(isset($appointment_id) && $appointment_id)
                                    <x-tailadmin-button variant="secondary" size="lg" href="{{ route('public.appointment.show', ['slug' => $tenant->subdomain, 'appointment_id' => $appointment_id]) }}"
                                        class="border-primary text-primary bg-transparent hover:bg-primary/10">
                                        <i class="mdi mdi-eye"></i>
                                        Ver Agendamento
                                    </x-tailadmin-button>
                                @endif
                                <x-tailadmin-button variant="primary" size="lg" href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}">
                                    <i class="mdi mdi-calendar-plus"></i>
                                    Fazer Novo Agendamento
                                </x-tailadmin-button>
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
@endsection

