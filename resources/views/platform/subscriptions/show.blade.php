@extends('layouts.freedash.app')
@section('title', 'Visualizar Assinaturas')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes da Assinatura</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.subscriptions.index') }}"
                                    class="text-muted">Assinaturas</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Detalhes</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.subscriptions.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
            <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title mb-4">Informações da Assinatura</h4>
                @php
                    $tenant = $subscription->tenant;
                    $grantsAccess = $tenant?->subscriptionGrantsAccess($subscription) ?? false;
                @endphp

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Tenant</label>
                        <p>{{ $subscription->tenant->trade_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Plano</label>
                        <p>
                            {{ $subscription->plan->name ?? '-' }}
                            @if ($subscription->plan)
                                <span class="badge {{ $subscription->plan->planTypeBadgeClass() }} ms-1">
                                    {{ $subscription->plan->planTypeLabel() }}
                                </span>
                                <span class="badge {{ $subscription->plan->landingVisibilityBadgeClass() }} ms-1">
                                    {{ $subscription->plan->landingVisibilityLabel() }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Início</label>
                        <p>{{ $subscription->starts_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Fim</label>
                        <p>{{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Dia de Vencimento</label>
                        <p>{{ $subscription->due_day }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Status</label>
                        <p>
                            <span
                                class="badge 
                @if ($subscription->status == 'active') bg-success
                @elseif($subscription->status == 'past_due') bg-warning
                @elseif($subscription->status == 'canceled') bg-danger
                @else bg-info @endif">
                                {{ $subscription->statusLabel() }}
                            </span>
                        </p>
                    </div>

                    {{-- 🔹 Novo campo: Método de Pagamento --}}
                    <div class="col-md-4">
                        <label class="fw-bold">Método de Pagamento</label>
                        <p>{{ $subscription->payment_method_label ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Renovação Automática</label>
                        <p>{{ $subscription->auto_renew ? 'Sim' : 'Não' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Esta assinatura libera acesso?</label>
                        <p>
                            <span class="badge {{ $grantsAccess ? 'bg-success' : 'bg-secondary' }}">
                                {{ $grantsAccess ? 'Sim, libera acesso' : 'Não libera acesso' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Situação comercial da tenant</label>
                        <p>
                            @if ($tenant)
                                <span class="badge {{ $tenant->commercialAccessSummaryBadgeClass() }}">
                                    {{ $tenant->commercialAccessSummaryLabel() }}
                                </span>
                                @if (! $tenant->isEligibleForAccess())
                                    <small class="d-block text-muted mt-1">
                                        {{ $tenant->commercialAccessStatusLabel() }}
                                    </small>
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4">Informações do Asaas</h4>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">ID Asaas</label>
                        <p>{{ $subscription->asaas_subscription_id ?? '—' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Status Sincronização</label>
                        <p>
                            <span
                                class="badge 
                        @if ($subscription->asaas_synced) bg-success 
                        @else bg-danger @endif">
                                {{ $subscription->asaas_synced ? 'Sincronizado' : 'Não sincronizado' }}
                            </span>
                        </p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Status Asaas</label>
                        <p>{{ strtoupper($subscription->asaas_sync_status ?? '—') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Última Sincronização</label>
                        <p>{{ $subscription->asaas_last_sync_at ? $subscription->asaas_last_sync_at->format('d/m/Y H:i') : '—' }}
                        </p>
                    </div>

                    <div class="col-md-8">
                        <label class="fw-bold">Último Erro</label>
                        <p class="text-danger">{{ $subscription->asaas_last_error ?? '—' }}</p>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                            <form action="{{ route('Platform.subscriptions.sync', $subscription) }}" method="POST"
                                class="m-0 p-0">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-1"></i> Sincronizar com Asaas
                                </button>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
