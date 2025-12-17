@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Conta')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-wallet text-primary me-2"></i>
            Detalhes da Conta
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
                    <a href="{{ workspace_route('tenant.finance.accounts.index') }}">Contas</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $account->name }}</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo:</strong>
                            @if($account->type === 'cash')
                                <span class="badge bg-info">Dinheiro</span>
                            @elseif($account->type === 'bank')
                                <span class="badge bg-primary">Banco</span>
                            @elseif($account->type === 'pix')
                                <span class="badge bg-success">PIX</span>
                            @else
                                <span class="badge bg-warning">Crédito</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            @if($account->active)
                                <span class="badge bg-success">Ativa</span>
                            @else
                                <span class="badge bg-secondary">Inativa</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Saldo Inicial:</strong>
                            <p class="mb-0">R$ {{ number_format($account->initial_balance, 2, ',', '.') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Saldo Atual:</strong>
                            <p class="mb-0 fs-5 fw-bold text-primary">R$ {{ number_format($account->current_balance, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ workspace_route('tenant.finance.accounts.edit', ['slug' => tenant()->subdomain, 'account' => $account->id]) }}" 
                           class="btn btn-primary">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                        <a href="{{ workspace_route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

