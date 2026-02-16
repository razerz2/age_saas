@extends('layouts.tailadmin.app')

@section('title', 'Minha Assinatura')

@section('content')

    <div class="page-header">
        <h3 class="page-title">Minha Assinatura</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Assinatura</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="mdi mdi-information-outline me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if (!$subscription)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="mdi mdi-information-outline" style="font-size: 4rem; color: #6c757d;"></i>
                        <h4 class="mt-3 mb-2">Nenhuma assinatura encontrada</h4>
                        <p class="text-muted">Entre em contato com o suporte para mais informações sobre sua assinatura.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Informações da Assinatura --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-credit-card me-2"></i>Informações da Assinatura
                        </h4>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Plano</label>
                                <p class="fs-5 mb-0">{{ $subscription->plan->name ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Status</label>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($subscription->status == 'active') bg-success
                                        @elseif($subscription->status == 'past_due') bg-warning
                                        @elseif($subscription->status == 'canceled') bg-danger
                                        @else bg-info @endif">
                                        {{ $subscription->statusLabel() }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Data de Início</label>
                                <p>{{ $subscription->starts_at ? $subscription->starts_at->format('d/m/Y') : '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Data de Término</label>
                                <p>{{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : 'Sem término' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Dia de Vencimento</label>
                                <p>{{ $subscription->due_day ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Renovação Automática</label>
                                <p>
                                    @if($subscription->auto_renew)
                                        <span class="badge bg-success">Ativada</span>
                                    @else
                                        <span class="badge bg-secondary">Desativada</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Método de Pagamento</label>
                                <p>{{ $subscription->payment_method_label ?? '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-muted">Valor do Plano</label>
                                <p class="fs-5 mb-0 text-primary">
                                    {{ $subscription->plan->formatted_price ?? '—' }}
                                </p>
                            </div>
                        </div>

                        @if(isset($pendingPlanChangeRequest) && $pendingPlanChangeRequest)
                            <div class="alert alert-info mt-3">
                                <i class="mdi mdi-information-outline me-2"></i>
                                <strong>Solicitação de Mudança Pendente:</strong>
                                <p class="mb-0 mt-2">
                                    Você possui uma solicitação para mudar para o plano 
                                    <strong>{{ $pendingPlanChangeRequest->requestedPlan->name ?? '—' }}</strong>.
                                    Aguardando aprovação do administrador.
                                </p>
                            </div>
                        @else
                            <div class="mt-3">
                                <div class="flex items-center justify-end gap-3 flex-nowrap">
                                    <x-tailadmin-button variant="primary" size="md" href="{{ workspace_route('tenant.plan-change-request.create') }}" class="inline-flex items-center gap-2">
                                        <i class="mdi mdi-swap-horizontal"></i> Solicitar Mudança de Plano
                                    </x-tailadmin-button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Funcionalidades do Plano --}}
        @if(!empty($planFeatures) && count($planFeatures) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-star-outline me-2"></i>Funcionalidades Inclusas
                        </h4>
                        <div class="row">
                            @foreach($planFeatures as $feature)
                            <div class="col-md-6 mb-2">
                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                <span>{{ $feature }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Faturas em Aberto --}}
        @if($pendingInvoices && $pendingInvoices->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-body">
                        <h4 class="card-title mb-4 text-warning">
                            <i class="mdi mdi-alert-circle me-2"></i>Faturas em Aberto
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Valor</th>
                                        <th>Vencimento</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingInvoices as $invoice)
                                    <tr>
                                        <td class="fw-bold">{{ $invoice->formatted_amount }}</td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '—' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($invoice->status == 'paid') bg-success
                                                @elseif($invoice->status == 'overdue') bg-danger
                                                @else bg-warning @endif">
                                                @if($invoice->status == 'paid') Paga
                                                @elseif($invoice->status == 'overdue') Vencida
                                                @else Pendente @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($invoice->payment_link)
                                                <x-tailadmin-button variant="primary" size="sm" href="{{ $invoice->payment_link }}" target="_blank"
                                                    class="px-3 py-1">
                                                    <i class="mdi mdi-link"></i> Pagar
                                                </x-tailadmin-button>
                                            @endif
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
        @endif

        {{-- Histórico de Faturas --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-file-document-outline me-2"></i>Histórico de Faturas
                        </h4>
                        @if($invoices && $invoices->count() > 0)
                        <div>
                            <table class="table table-hover" id="datatable-list">
                                <thead>
                                    <tr>
                                        <th>Valor</th>
                                        <th>Vencimento</th>
                                        <th>Status</th>
                                        <th>Método de Pagamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                    <tr>
                                        <td class="fw-bold">{{ $invoice->formatted_amount }}</td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '—' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($invoice->status == 'paid') bg-success
                                                @elseif($invoice->status == 'overdue') bg-danger
                                                @elseif($invoice->status == 'canceled') bg-secondary
                                                @else bg-warning @endif">
                                                @if($invoice->status == 'paid') Paga
                                                @elseif($invoice->status == 'overdue') Vencida
                                                @elseif($invoice->status == 'canceled') Cancelada
                                                @else Pendente @endif
                                            </span>
                                        </td>
                                        <td>{{ $invoice->payment_method ?? '—' }}</td>
                                        <td>
                                            @if($invoice->payment_link && $invoice->status != 'paid')
                                                <x-tailadmin-button variant="primary" size="sm" href="{{ $invoice->payment_link }}" target="_blank"
                                                    class="px-3 py-1">
                                                    <i class="mdi mdi-link"></i> Pagar
                                                </x-tailadmin-button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted text-center py-4">Nenhuma fatura encontrada.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $('#datatable-list').length) {
        $('#datatable-list').DataTable({
            pageLength: 25,
            responsive: true,
            autoWidth: false,
            scrollX: false,
            scrollCollapse: false,
            pagingType: "simple_numbers",
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json"
            }
        });
    }
});
</script>
@endpush

