@extends('layouts.freedash.app')
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

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Tenant</label>
                        <p>{{ $subscription->tenant->trade_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Plano</label>
                        <p>{{ $subscription->plan->name ?? '-' }}</p>
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
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
