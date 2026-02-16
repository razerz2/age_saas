@extends('layouts.freedash.app')
@section('title', 'Editar Invoices')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    Editar Fatura
                </h4>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- ✅ Alertas de sucesso --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ⚠️ Alertas de aviso --}}
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- 🔹 Exibição de erros de validação --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Ops!</strong> Verifique os erros abaixo:
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif
                <form method="POST" action="{{ route('Platform.invoices.update', $invoice->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Tenant e Assinatura (somente leitura) --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ $invoice->tenant->trade_name ?? $invoice->tenant->legal_name }}" readonly>
                            <input type="hidden" name="tenant_id" value="{{ $invoice->tenant_id }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Assinatura</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ $invoice->subscription->plan->name ?? 'N/A' }}" readonly>
                            <input type="hidden" name="subscription_id" value="{{ $invoice->subscription_id }}">
                        </div>
                    </div>

                    {{-- Valor, vencimento e status --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Valor (R$)</label>
                            <input type="text" id="amount_display" class="form-control"
                                value="{{ 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.') }}">
                            <input type="hidden" name="amount_cents" id="amount_cents"
                                value="{{ $invoice->amount_cents }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Vencimento</label>
                            <input type="date" name="due_date" class="form-control"
                                value="{{ $invoice->due_date->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                @foreach (['pending' => 'Pendente', 'paid' => 'Pago', 'overdue' => 'Vencido', 'canceled' => 'Cancelado'] as $k => $v)
                                    <option value="{{ $k }}" {{ $invoice->status == $k ? 'selected' : '' }}>
                                        {{ $v }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Informações de sincronização (somente leitura) --}}
                    <div class="row mb-3 border-top pt-3 mt-4">
                        <div class="col-md-4">
                            <label class="form-label">Status de Sincronização</label><br>
                            @if ($invoice->asaas_synced)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> {{ ucfirst($invoice->asaas_sync_status) }}
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ ucfirst($invoice->asaas_sync_status) }}
                                </span>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Última Sincronização</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ $invoice->asaas_last_sync_at ? $invoice->asaas_last_sync_at->format('d/m/Y H:i') : '-' }}"
                                readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ID no Asaas</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ $invoice->asaas_payment_id ?? '-' }}" readonly>
                        </div>
                    </div>

                    {{-- Mensagem de erro da última sincronização --}}
                    @if ($invoice->asaas_last_error)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Último erro:</strong> {{ $invoice->asaas_last_error }}
                        </div>
                    @endif

                    {{-- Botões --}}
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save me-2"></i>Salvar Alterações
                        </button>
                        <a href="{{ route('Platform.invoices.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountDisplay = document.getElementById('amount_display');
            const amountCents = document.getElementById('amount_cents');

            amountDisplay.addEventListener('input', function() {
                let value = amountDisplay.value.replace(/[^\d]/g, '');
                if (value === '') {
                    amountCents.value = '';
                    return;
                }

                let numericValue = parseInt(value);
                let formatted = (numericValue / 100).toFixed(2).replace('.', ',');
                formatted = 'R$ ' + formatted.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                amountDisplay.value = formatted;

                amountCents.value = numericValue;
            });
        });
    </script>
@endpush
