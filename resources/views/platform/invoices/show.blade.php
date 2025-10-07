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
            <h4 class="mb-4">{{ $invoice->tenant->trade_name ?? 'Tenant removido' }}</h4>

            <dl class="row">
                <dt class="col-sm-3">Valor</dt>
                <dd class="col-sm-9">{{ $invoice->formatted_amount }}</dd>

                <dt class="col-sm-3">Vencimento</dt>
                <dd class="col-sm-9">{{ $invoice->due_date->format('d/m/Y') }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9"><span class="badge bg-info">{{ ucfirst($invoice->status) }}</span></dd>

                <dt class="col-sm-3">Provedor</dt>
                <dd class="col-sm-9">{{ $invoice->provider ?? '-' }}</dd>

                <dt class="col-sm-3">Link de Pagamento</dt>
                <dd class="col-sm-9">
                    @if ($invoice->payment_link)
                        <a href="{{ $invoice->payment_link }}" target="_blank">{{ $invoice->payment_link }}</a>
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-3">Criado em</dt>
                <dd class="col-sm-9">{{ $invoice->created_at->format('d/m/Y H:i') }}</dd>
            </dl>

            <a href="{{ route('Platform.invoices.edit', $invoice->id) }}" class="btn btn-warning"><i class="fa fa-edit"></i> Editar</a>
            <a href="{{ route('Platform.invoices.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
</div>

@include('layouts.freedash.footer')
@endsection
