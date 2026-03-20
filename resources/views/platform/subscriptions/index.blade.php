@extends('layouts.freedash.app')
@section('title', 'Listar Assinaturas')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Assinaturas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
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

                        {{-- ✅ Alertas de sucesso --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- ⚠️ Alertas de aviso --}}
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- ❌ Erros gerais (via withErrors ou validação) --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-times-circle me-1"></i>
                                <strong>Ops!</strong> Verifique os erros abaixo.<br>
                                @foreach ($errors->all() as $error)
                                    <span class="d-block">• {{ $error }}</span>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif


                        <div class="table-responsive">
                            <table id="subscriptions_table"
                                class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tenant</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Acesso da Tenant</th>
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
                                            <td>
                                                {{ $subscription->plan->name ?? '-' }}
                                                @if ($subscription->plan)
                                                    <div class="mt-1">
                                                        <span class="badge {{ $subscription->plan->planTypeBadgeClass() }}">
                                                            {{ $subscription->plan->planTypeLabel() }}
                                                        </span>
                                                        <span
                                                            class="badge {{ $subscription->plan->landingVisibilityBadgeClass() }}">
                                                            {{ $subscription->plan->landingVisibilityLabel() }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge 
                                                @if ($subscription->status == 'active') bg-success
                                                @elseif($subscription->status == 'past_due') bg-warning
                                                @elseif($subscription->status == 'canceled') bg-danger
                                                @else bg-info @endif">
                                                    {{ $subscription->statusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $tenant = $subscription->tenant;
                                                    $grantsAccess = $tenant?->subscriptionGrantsAccess($subscription) ?? false;
                                                @endphp

                                                <span class="badge {{ $grantsAccess ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $grantsAccess ? 'Libera acesso' : 'Nao libera acesso' }}
                                                </span>

                                                @if ($tenant)
                                                    <small class="d-block text-muted mt-1">
                                                        {{ $tenant->commercialAccessSummaryLabel() }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>{{ $subscription->starts_at->format('d/m/Y') }}</td>
                                            <td>{{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : '-' }}
                                            </td>
                                            <td>{{ $subscription->auto_renew ? 'Sim' : 'Não' }}</td>
                                            <td class="text-center">
                                                <a title="Visualizar"
                                                    href="{{ route('Platform.subscriptions.show', $subscription->id) }}"
                                                    class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a title="Editar"
                                                    href="{{ route('Platform.subscriptions.edit', $subscription->id) }}"
                                                    class="btn btn-sm btn-warning text-white">
                                                    <i class="fa fa-edit"></i>
                                                </a>

                                                {{-- 🔄 Botão de sincronização só aparece se houver erro ou pendência --}}
                                                @if (in_array($subscription->asaas_sync_status, ['failed', 'pending']))
                                                    <form
                                                        action="{{ route('Platform.subscriptions.sync', $subscription) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning"
                                                            title="Tentar sincronizar novamente com Asaas">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- 🧾 Novo botão para gerar fatura --}}
                                                @if ($subscription->is_expired && !$subscription->has_pending_invoice)
                                                    <form
                                                        action="{{ route('Platform.subscriptions.renew', $subscription->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirmSubmit(event, 'Deseja gerar uma nova fatura para esta assinatura?', 'Gerar Nova Fatura')">
                                                        @csrf
                                                        <button type="submit" title="Nova Fatura" class="btn btn-sm btn-success">
                                                            <i class="fas fa-dollar-sign"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form
                                                    action="{{ route('Platform.subscriptions.destroy', $subscription->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirmSubmit(event, 'Deseja realmente excluir esta assinatura? Esta ação não pode ser desfeita.', 'Confirmar Exclusão')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Exclusão" class="btn btn-sm btn-danger">
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
    @include('layouts.freedash.footer')
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
