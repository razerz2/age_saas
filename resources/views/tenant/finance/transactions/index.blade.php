@extends('layouts.connect_plus.app')

@section('title', 'Transações Financeiras')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash-multiple text-primary me-2"></i>
            Transações Financeiras
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Transações</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Transações</h4>

                    <div class="d-flex justify-content-between mb-3">
                        <a href="{{ workspace_route('tenant.finance.transactions.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus"></i> Nova Transação
                        </a>

                        <form method="GET" class="d-flex gap-2">
                            <select name="type" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Todos os tipos</option>
                                <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Receita</option>
                                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Despesa</option>
                            </select>
                            <select name="status" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Todos os status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            <input type="date" name="date_from" class="form-control form-control-sm" 
                                   value="{{ request('date_from') }}" placeholder="De">
                            <input type="date" name="date_to" class="form-control form-control-sm" 
                                   value="{{ request('date_to') }}" placeholder="Até">
                            <button type="submit" class="btn btn-sm btn-secondary">Filtrar</button>
                            <a href="{{ workspace_route('tenant.finance.transactions.index', ['slug' => tenant()->subdomain]) }}" 
                               class="btn btn-sm btn-outline-secondary">Limpar</a>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Conta</th>
                                    <th>Categoria</th>
                                    <th style="width: 150px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date->format('d/m/Y') }}</td>
                                        <td>{{ Str::limit($transaction->description, 50) }}</td>
                                        <td>
                                            @if($transaction->type === 'income')
                                                <span class="badge bg-success">Receita</span>
                                            @else
                                                <span class="badge bg-danger">Despesa</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->type === 'income' ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($transaction->status === 'paid')
                                                <span class="badge bg-success">Pago</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="badge bg-warning">Pendente</span>
                                            @else
                                                <span class="badge bg-secondary">Cancelado</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->account->name ?? 'N/A' }}</td>
                                        <td>{{ $transaction->category->name ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.finance.transactions.show', ['slug' => tenant()->subdomain, 'transaction' => $transaction->id]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ workspace_route('tenant.finance.transactions.edit', ['slug' => tenant()->subdomain, 'transaction' => $transaction->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Nenhuma transação encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

