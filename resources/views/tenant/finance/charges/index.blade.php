@extends('layouts.connect_plus.app')

@section('title', 'Cobranças')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-credit-card text-primary me-2"></i>
            Cobranças
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Cobranças</li>
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
                    <h4 class="card-title">Lista de Cobranças</h4>

                    <form method="GET" class="d-flex gap-2 mb-3">
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Todos os status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirado</option>
                        </select>
                        <select name="origin" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Todas as origens</option>
                            <option value="public" {{ request('origin') === 'public' ? 'selected' : '' }}>Público</option>
                            <option value="portal" {{ request('origin') === 'portal' ? 'selected' : '' }}>Portal</option>
                            <option value="internal" {{ request('origin') === 'internal' ? 'selected' : '' }}>Interno</option>
                        </select>
                        <input type="date" name="date_from" class="form-control form-control-sm" 
                               value="{{ request('date_from') }}" placeholder="De">
                        <input type="date" name="date_to" class="form-control form-control-sm" 
                               value="{{ request('date_to') }}" placeholder="Até">
                        <button type="submit" class="btn btn-sm btn-secondary">Filtrar</button>
                        <a href="{{ workspace_route('tenant.finance.charges.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-sm btn-outline-secondary">Limpar</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Agendamento</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                    <th>Origem</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($charges as $charge)
                                    <tr>
                                        <td>{{ $charge->patient->full_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($charge->appointment)
                                                {{ $charge->appointment->starts_at->format('d/m/Y H:i') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td><strong>R$ {{ number_format($charge->amount, 2, ',', '.') }}</strong></td>
                                        <td>{{ $charge->due_date->format('d/m/Y') }}</td>
                                        <td>
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
                                        </td>
                                        <td>
                                            @if($charge->origin === 'public')
                                                <span class="badge bg-info">Público</span>
                                            @elseif($charge->origin === 'portal')
                                                <span class="badge bg-primary">Portal</span>
                                            @else
                                                <span class="badge bg-secondary">Interno</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.finance.charges.show', ['slug' => tenant()->subdomain, 'charge' => $charge->id]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            @if(auth()->guard('tenant')->user()->role === 'admin' && $charge->status === 'pending')
                                                <form action="{{ workspace_route('tenant.finance.charges.cancel', ['slug' => tenant()->subdomain, 'charge' => $charge->id]) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Tem certeza que deseja cancelar esta cobrança?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="mdi mdi-cancel"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhuma cobrança encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $charges->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

