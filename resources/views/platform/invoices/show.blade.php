@extends('layouts.freedash.app')
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes da Fatura</h4>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            {{-- Cabeçalho --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                    {{ $invoice->tenant->trade_name ?? 'Tenant removido' }}
                </h4>

                <div>
                    <a href="{{ route('Platform.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <a href="{{ route('Platform.invoices.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>

            {{-- Informações principais --}}
            <h5 class="text-primary fw-bold mb-3">
                <i class="fas fa-info-circle me-2"></i> Informações Gerais
            </h5>
            <dl class="row mb-4">
                <dt class="col-sm-3">Valor</dt>
                <dd class="col-sm-9">{{ $invoice->formatted_amount }}</dd>

                <dt class="col-sm-3">Vencimento</dt>
                <dd class="col-sm-9">{{ $invoice->due_date->format('d/m/Y') }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @php
                        $colors = [
                            'pending' => 'info',
                            'paid' => 'success',
                            'overdue' => 'warning',
                            'canceled' => 'danger',
                        ];
                    @endphp
                    <span class="badge bg-{{ $colors[$invoice->status] ?? 'secondary' }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </dd>

                <dt class="col-sm-3">Criado em</dt>
                <dd class="col-sm-9">{{ $invoice->created_at->format('d/m/Y H:i') }}</dd>
            </dl>

            {{-- Informações do provedor --}}
            <h5 class="text-primary fw-bold mb-3">
                <i class="fas fa-link me-2"></i> Informações do Pagamento
            </h5>
            <dl class="row mb-4">
                <dt class="col-sm-3">Provedor</dt>
                <dd class="col-sm-9">{{ strtoupper($invoice->provider ?? '-') }}</dd>

                <dt class="col-sm-3">ID no Gateway</dt>
                <dd class="col-sm-9">{{ $invoice->provider_id ?? '-' }}</dd>

                <dt class="col-sm-3">Link de Pagamento</dt>
                <dd class="col-sm-9">
                    @if ($invoice->payment_link)
                        <a href="{{ $invoice->payment_link }}" target="_blank" class="text-decoration-none">
                            <i class="fas fa-external-link-alt me-1 text-primary"></i>
                            {{ $invoice->payment_link }}
                        </a>
                    @else
                        -
                    @endif
                </dd>
            </dl>

            {{-- Auditoria de sincronização Asaas --}}
            <h5 class="text-primary fw-bold mb-3">
                <i class="fas fa-sync-alt me-2"></i> Integração Asaas
            </h5>
            <dl class="row mb-4">
                <dt class="col-sm-3">ID no Asaas</dt>
                <dd class="col-sm-9">{{ $invoice->asaas_payment_id ?? '-' }}</dd>

                <dt class="col-sm-3">Status de Sincronização</dt>
                <dd class="col-sm-9">
                    @if ($invoice->asaas_synced)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> {{ ucfirst($invoice->asaas_sync_status) }}
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ ucfirst($invoice->asaas_sync_status ?? 'failed') }}
                        </span>
                    @endif
                </dd>

                <dt class="col-sm-3">Última Sincronização</dt>
                <dd class="col-sm-9">
                    {{ $invoice->asaas_last_sync_at ? $invoice->asaas_last_sync_at->format('d/m/Y H:i') : '-' }}
                </dd>

                @if ($invoice->asaas_last_error)
                    <dt class="col-sm-3 text-danger">Último Erro</dt>
                    <dd class="col-sm-9 text-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        {{ $invoice->asaas_last_error }}
                    </dd>
                @endif
            </dl>
        </div>
    </div>
</div>

@include('layouts.freedash.footer')
@endsection
