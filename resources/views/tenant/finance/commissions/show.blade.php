@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Comissão')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash-usd text-primary me-2"></i>
            Detalhes da Comissão
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.commissions.index') }}">Comissões</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Comissão #{{ substr($commission->id, 0, 8) }}</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Médico:</strong>
                            <p class="mb-0">{{ $commission->doctor->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Valor da Comissão:</strong>
                            <p class="mb-0 fs-5 fw-bold text-primary">R$ {{ number_format($commission->amount, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Percentual:</strong>
                            <p class="mb-0">{{ number_format($commission->percentage, 2, ',', '.') }}%</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p class="mb-0">
                                @if($commission->status === 'paid')
                                    <span class="badge bg-success">Pago</span>
                                @else
                                    <span class="badge bg-warning">Pendente</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($commission->transaction)
                        <div class="mb-3">
                            <strong>Transação Relacionada:</strong>
                            <p class="mb-0">
                                R$ {{ number_format($commission->transaction->amount, 2, ',', '.') }}
                                @if($commission->transaction->appointment)
                                    - Agendamento em {{ $commission->transaction->appointment->starts_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($commission->paid_at)
                        <div class="mb-3">
                            <strong>Data de Pagamento:</strong>
                            <p class="mb-0">{{ $commission->paid_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        @if(auth()->guard('tenant')->user()->role === 'admin' && !$commission->isPaid())
                            <form action="{{ workspace_route('tenant.finance.commissions.markPaid', ['slug' => tenant()->subdomain, 'commission' => $commission->id]) }}" 
                                  method="POST" class="d-inline" 
                                  onsubmit="return confirm('Tem certeza que deseja marcar esta comissão como paga?');">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check"></i> Marcar como Paga
                                </button>
                            </form>
                        @endif

                        <a href="{{ workspace_route('tenant.finance.commissions.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

