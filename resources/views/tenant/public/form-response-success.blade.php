@extends('layouts.tailadmin.public')

@section('title', 'Formulário Enviado — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-8">
            <div class="pt-10 pb-6 sm:pt-12 sm:pb-8 lg:pt-14 text-center">
                <div class="mx-auto mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="mdi mdi-check-circle text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Formulário Enviado com Sucesso!</h1>
                <p class="mt-2 text-sm text-slate-600">Sua resposta foi registrada. Obrigado por preencher o formulário.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="px-6 py-6">
                    <div class="mx-auto w-full max-w-xl text-center">
                        <div class="mt-2 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            @if($response->appointment)
                                <a
                                    href="{{ tenant_route($tenant, 'public.appointment.show', ['appointment_id' => $response->appointment->id]) }}"
                                    class="btn btn-primary"
                                >
                                    <i class="mdi mdi-calendar-clock text-lg text-white"></i>
                                    Ver Agendamento
                                </a>
                            @endif

                            <a
                                href="{{ tenant_route($tenant, 'public.patient.identify') }}"
                                class="btn btn-outline"
                            >
                                <i class="mdi mdi-home text-lg text-slate-900"></i>
                                Voltar ao Início
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
