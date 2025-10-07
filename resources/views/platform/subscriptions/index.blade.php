@extends('layouts.freedash.app')
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Assinaturas</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">Assinaturas</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-5 align-self-center">
            <div class="customize-input float-end">
                <a href="{{ route('Platform.subscriptions.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fa fa-plus me-1"></i> Nova Assinatura
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Lista de Assinaturas</h4>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="subscriptions_table" class="table table-striped table-bordered text-nowrap align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tenant</th>
                                    <th>Plano</th>
                                    <th>Status</th>
                                    <th>Início</th>
                                    <th>Vencimento</th>
                                    <th>Renovação</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subscriptions as $subscription)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $subscription->tenant->trade_name ?? '-' }}</td>
                                        <td>{{ $subscription->plan->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($subscription->status == 'active') bg-success
                                                @elseif($subscription->status == 'past_due') bg-warning
                                                @elseif($subscription->status == 'canceled') bg-danger
                                                @else bg-info @endif">
                                                {{ $subscription->statusLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ $subscription->starts_at->format('d/m/Y') }}</td>
                                        <td>{{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $subscription->auto_renew ? 'Sim' : 'Não' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('Platform.subscriptions.show', $subscription->id) }}" class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('Platform.subscriptions.edit', $subscription->id) }}" class="btn btn-sm btn-warning text-white">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('Platform.subscriptions.destroy', $subscription->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Deseja realmente excluir esta assinatura?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include("layouts.freedash.footer")
@endsection

@push('scripts')
<script>
    $(function() {
        $('#subscriptions_table').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
            }
        });
    });
</script>
@endpush
