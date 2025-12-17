@extends('layouts.connect_plus.app')

@section('title', 'Contas Financeiras')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-wallet text-primary me-2"></i>
            Contas Financeiras
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Contas</li>
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
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Contas</h4>

                    <a href="{{ workspace_route('tenant.finance.accounts.create') }}" class="btn btn-primary mb-3">
                        <i class="mdi mdi-plus"></i> Nova Conta
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Saldo Inicial</th>
                                    <th>Saldo Atual</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($accounts as $account)
                                    <tr>
                                        <td>{{ $account->name }}</td>
                                        <td>
                                            @if($account->type === 'cash')
                                                <span class="badge bg-info">Dinheiro</span>
                                            @elseif($account->type === 'bank')
                                                <span class="badge bg-primary">Banco</span>
                                            @elseif($account->type === 'pix')
                                                <span class="badge bg-success">PIX</span>
                                            @else
                                                <span class="badge bg-warning">Crédito</span>
                                            @endif
                                        </td>
                                        <td>R$ {{ number_format($account->initial_balance, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($account->current_balance, 2, ',', '.') }}</td>
                                        <td>
                                            @if($account->active)
                                                <span class="badge bg-success">Ativa</span>
                                            @else
                                                <span class="badge bg-secondary">Inativa</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.finance.accounts.show', ['slug' => tenant()->subdomain, 'account' => $account->id]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ workspace_route('tenant.finance.accounts.edit', ['slug' => tenant()->subdomain, 'account' => $account->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ workspace_route('tenant.finance.accounts.destroy', ['slug' => tenant()->subdomain, 'account' => $account->id]) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta conta?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhuma conta cadastrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $accounts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

