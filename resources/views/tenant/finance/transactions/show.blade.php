@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Transação')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash text-primary me-2"></i>
            Detalhes da Transação
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
                    <a href="{{ workspace_route('tenant.finance.transactions.index') }}">Transações</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Transação #{{ substr($transaction->id, 0, 8) }}</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo:</strong>
                            <p class="mb-0">
                                @if($transaction->type === 'income')
                                    <span class="badge bg-success">Receita</span>
                                @else
                                    <span class="badge bg-danger">Despesa</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Valor:</strong>
                            <p class="mb-0 fs-5 fw-bold {{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Data:</strong>
                            <p class="mb-0">{{ $transaction->date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p class="mb-0">
                                @if($transaction->status === 'paid')
                                    <span class="badge bg-success">Pago</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">Pendente</span>
                                @else
                                    <span class="badge bg-secondary">Cancelado</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Descrição:</strong>
                        <p class="mb-0">{{ $transaction->description }}</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Conta:</strong>
                            <p class="mb-0">{{ $transaction->account->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Categoria:</strong>
                            <p class="mb-0">{{ $transaction->category->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($transaction->patient)
                        <div class="mb-3">
                            <strong>Paciente:</strong>
                            <p class="mb-0">{{ $transaction->patient->full_name }}</p>
                        </div>
                    @endif

                    @if($transaction->doctor)
                        <div class="mb-3">
                            <strong>Médico:</strong>
                            <p class="mb-0">{{ $transaction->doctor->user->name ?? 'N/A' }}</p>
                        </div>
                    @endif

                    @if($transaction->appointment)
                        <div class="mb-3">
                            <strong>Agendamento:</strong>
                            <p class="mb-0">
                                {{ $transaction->appointment->starts_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif

                    @if($transaction->creator)
                        <div class="mb-3">
                            <strong>Criado por:</strong>
                            <p class="mb-0">{{ $transaction->creator->name }}</p>
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ workspace_route('tenant.finance.transactions.edit', ['slug' => tenant()->subdomain, 'transaction' => $transaction->id]) }}" 
                           class="btn btn-primary">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                        <a href="{{ workspace_route('tenant.finance.transactions.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

