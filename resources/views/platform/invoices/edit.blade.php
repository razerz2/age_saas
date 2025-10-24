@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Editar Fatura</h4>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('Platform.invoices.update', $invoice->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant</label>
                            <select name="tenant_id" class="form-control" required>
                                <option value="{{ $invoice->tenant->id }}" selected>
                                    {{ $invoice->tenant->legal_name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Assinatura</label>
                            <select name="subscription_id" class="form-control" required>
                                <option value="{{ $invoice->subscription->plan->id }}" selected>
                                        {{ $invoice->subscription->plan->name }}
                                </option>
                            </select>
                        </div>
                    </div>

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
                                        {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Link de Pagamento</label>
                            <input type="url" name="payment_link" class="form-control"
                                value="{{ $invoice->payment_link }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Provedor</label>
                            <input type="text" name="provider" class="form-control" value="{{ $invoice->provider }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ID no Gateway</label>
                            <input type="text" name="provider_id" class="form-control"
                                value="{{ $invoice->provider_id }}">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-4">Salvar Alterações</button>
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
