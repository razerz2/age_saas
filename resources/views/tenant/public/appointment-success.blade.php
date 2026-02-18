@extends('layouts.tailadmin.public')

@section('title', 'Agendamento Confirmado — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-8">
            <div class="pt-10 pb-6 sm:pt-12 sm:pb-8 lg:pt-14 text-center">
                <div class="mx-auto mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="mdi mdi-check-circle text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Agendamento Confirmado!</h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ session('success') ?: 'Seu agendamento foi realizado com sucesso.' }}
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="px-6 py-6">
                    <div class="mx-auto w-full max-w-xl text-center">
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                            <div class="flex items-start justify-center gap-2">
                                <i class="mdi mdi-information-outline text-base text-indigo-600"></i>
                                <p>Você receberá uma confirmação por e-mail em breve.</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            @if(isset($appointment_id) && $appointment_id)
                                <a
                                    href="{{ route('public.appointment.show', ['slug' => $tenant->subdomain, 'appointment_id' => $appointment_id]) }}"
                                    class="inline-flex w-auto min-w-[180px] items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                >
                                    <i class="mdi mdi-eye text-lg text-slate-900"></i>
                                    Ver Agendamento
                                </a>
                            @endif

                            <a
                                href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}"
                                class="inline-flex w-auto min-w-[180px] items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            >
                                <i class="mdi mdi-calendar-plus text-lg text-white"></i>
                                Fazer Novo Agendamento
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

