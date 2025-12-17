@extends('layouts.connect_plus.app')

@section('title', 'Comissões')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-cash-usd text-primary me-2"></i>
            Comissões
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Comissões</li>
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
                    <h4 class="card-title">Lista de Comissões</h4>

                    <form method="GET" class="d-flex gap-2 mb-3">
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Todos os status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                        </select>
                        @if(auth()->guard('tenant')->user()->role === 'admin' && $doctors)
                            <select name="doctor_id" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Todos os médicos</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') === $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="btn btn-sm btn-secondary">Filtrar</button>
                        <a href="{{ workspace_route('tenant.finance.commissions.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-sm btn-outline-secondary">Limpar</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Transação</th>
                                    <th>Percentual</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data de Pagamento</th>
                                    <th style="width: 150px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($commissions as $commission)
                                    <tr>
                                        <td>{{ $commission->doctor->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($commission->transaction)
                                                R$ {{ number_format($commission->transaction->amount, 2, ',', '.') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ number_format($commission->percentage, 2, ',', '.') }}%</td>
                                        <td><strong>R$ {{ number_format($commission->amount, 2, ',', '.') }}</strong></td>
                                        <td>
                                            @if($commission->status === 'paid')
                                                <span class="badge bg-success">Pago</span>
                                            @else
                                                <span class="badge bg-warning">Pendente</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($commission->paid_at)
                                                {{ $commission->paid_at->format('d/m/Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.finance.commissions.show', ['slug' => tenant()->subdomain, 'commission' => $commission->id]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhuma comissão encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $commissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

