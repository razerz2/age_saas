@extends('layouts.tailadmin.app')

@section('page', 'dashboard')

@section('content')
<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                Seu período de teste expirou.
            </p>
            <p class="text-sm text-red-700 dark:text-red-300">
                Seu acesso foi pausado. Escolha um plano para continuar usando o sistema.
            </p>
            @if(!empty($expiredTrialSubscription?->trial_ends_at))
                <p class="mt-1 text-xs text-red-700 dark:text-red-300">
                    Trial encerrado em {{ $expiredTrialSubscription->trial_ends_at->format('d/m/Y') }}.
                </p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ workspace_route('tenant.plan-change-request.create') }}"
                class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                Escolher plano
            </a>
            <a href="{{ workspace_route('tenant.subscription.show') }}"
                class="inline-flex items-center rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-700 dark:text-red-200 dark:hover:bg-red-900/30">
                Regularizar agora
            </a>
        </div>
    </div>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Acesso limitado até a regularização</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
        Você ainda pode consultar esta área e avançar com o upgrade, mas as funcionalidades principais ficarão bloqueadas
        até a ativação comercial de uma assinatura paga.
    </p>

    @if(!empty($blockedMessage))
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
            {{ $blockedMessage }}
        </div>
    @endif
</div>
@endsection
