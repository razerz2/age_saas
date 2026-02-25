@extends('layouts.tailadmin.public')

@section('title', 'Oferta de Vaga - ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5 text-center">
                    <h1 class="text-xl font-semibold text-slate-900">Oferta de vaga na fila de espera</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Confira os detalhes da vaga e confirme para reservar o horário.
                    </p>
                </div>

                <div class="space-y-5 px-6 py-6">
                    @if (session('error'))
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @php
                        $doctorName = optional(optional($entry->doctor)->user)->name_full
                            ?? optional(optional($entry->doctor)->user)->name
                            ?? 'Profissional';
                    @endphp

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Profissional</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ $doctorName }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Início</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($entry->starts_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Término</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($entry->ends_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Oferta válida até</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($entry->offer_expires_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if ($isOfferValid)
                        <form method="POST" action="{{ route('public.waitlist.offer.accept', ['slug' => $tenant->subdomain, 'token' => $entry->offer_token]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-check text-lg text-white"></i>
                                Confirmar vaga
                            </button>
                        </form>
                    @else
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Esta oferta não está mais disponível.
                        </div>
                    @endif

                    <a href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}" class="btn btn-outline">
                        <i class="mdi mdi-arrow-left text-lg text-slate-900"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

