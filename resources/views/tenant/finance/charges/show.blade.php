@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Cobrança')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-credit-card text-primary me-2"></i>
            Detalhes da Cobrança
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
                    <a href="{{ workspace_route('tenant.finance.charges.index') }}">Cobranças</a>
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
                    <h4 class="card-title">Cobrança #{{ substr($charge->id, 0, 8) }}</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Paciente:</strong>
                            <p class="mb-0">{{ $charge->patient->full_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Valor:</strong>
                            <p class="mb-0 fs-5 fw-bold text-primary">R$ {{ number_format($charge->amount, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p class="mb-0">
                                @if($charge->status === 'paid')
                                    <span class="badge bg-success">Pago</span>
                                @elseif($charge->status === 'pending')
                                    @if($charge->isOverdue())
                                        <span class="badge bg-danger">Vencido</span>
                                    @else
                                        <span class="badge bg-warning">Pendente</span>
                                    @endif
                                @elseif($charge->status === 'cancelled')
                                    <span class="badge bg-secondary">Cancelado</span>
                                @else
                                    <span class="badge bg-dark">{{ ucfirst($charge->status) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Vencimento:</strong>
                            <p class="mb-0">{{ $charge->due_date->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Origem:</strong>
                            <p class="mb-0">
                                @if($charge->origin === 'public')
                                    <span class="badge bg-info">Público</span>
                                @elseif($charge->origin === 'portal')
                                    <span class="badge bg-primary">Portal</span>
                                @else
                                    <span class="badge bg-secondary">Interno</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Tipo de Cobrança:</strong>
                            <p class="mb-0">
                                @if($charge->billing_type === 'reservation')
                                    <span class="badge bg-info">Reserva</span>
                                @else
                                    <span class="badge bg-primary">Integral</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($charge->appointment)
                        <div class="mb-3">
                            <strong>Agendamento:</strong>
                            <p class="mb-0">
                                {{ $charge->appointment->starts_at->format('d/m/Y H:i') }}
                                @if($charge->appointment->doctor)
                                    - Dr(a). {{ $charge->appointment->doctor->user->name ?? 'N/A' }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($charge->payment_link)
                        <div class="mb-3">
                            <strong>Link de Pagamento:</strong>
                            <p class="mb-0">
                                <a href="{{ $charge->payment_link }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-link"></i> Abrir Link
                                </a>
                            </p>
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        @if(auth()->guard('tenant')->user()->role !== 'doctor' && $charge->status === 'pending')
                            <form action="{{ workspace_route('tenant.finance.charges.resend', ['slug' => tenant()->subdomain, 'charge' => $charge->id]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="mdi mdi-email-send"></i> Reenviar Link
                                </button>
                            </form>
                        @endif

                        @if(auth()->guard('tenant')->user()->role === 'admin' && $charge->status === 'pending')
                            <form action="{{ workspace_route('tenant.finance.charges.cancel', ['slug' => tenant()->subdomain, 'charge' => $charge->id]) }}" 
                                  method="POST" class="d-inline" 
                                  onsubmit="return confirm('Tem certeza que deseja cancelar esta cobrança?');">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="mdi mdi-cancel"></i> Cancelar
                                </button>
                            </form>
                        @endif

                        <a href="{{ workspace_route('tenant.finance.charges.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

