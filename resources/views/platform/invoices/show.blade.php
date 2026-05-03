@extends('layouts.freedash.app')
@section('title', 'Visualizar Faturas')

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
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @if ($errors->has('general'))
                            {{ $errors->first('general') }}
                        @else
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

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

                <h5 class="text-primary fw-bold mb-3">
                    <i class="fas fa-info-circle me-2"></i> Informacoes Gerais
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
                            $canShowSyncButton =
                                in_array($invoice->asaas_sync_status, ['failed', 'pending'], true) ||
                                (empty($invoice->provider_id) && in_array($invoice->status, ['pending', 'overdue'], true));
                        @endphp
                        <span class="badge bg-{{ $colors[$invoice->status] ?? 'secondary' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">Metodo de Pagamento</dt>
                    <dd class="col-sm-9">{{ $invoice->payment_method ?? '-' }}</dd>

                    <dt class="col-sm-3">Criado em</dt>
                    <dd class="col-sm-9">{{ $invoice->created_at->format('d/m/Y H:i') }}</dd>
                </dl>

                <h5 class="text-primary fw-bold mb-3">
                    <i class="fas fa-link me-2"></i> Informacoes do Asaas
                </h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">Provedor</dt>
                    <dd class="col-sm-9">{{ strtoupper($invoice->provider ?? '-') }}</dd>

                    <dt class="col-sm-3">Provider ID</dt>
                    <dd class="col-sm-9">{{ $invoice->provider_id ?? '-' }}</dd>

                    <dt class="col-sm-3">Asaas Payment ID</dt>
                    <dd class="col-sm-9">{{ $invoice->asaas_payment_id ?? '-' }}</dd>

                    <dt class="col-sm-3">Status Sync</dt>
                    <dd class="col-sm-9">{{ strtoupper($invoice->asaas_sync_status ?? '-') }}</dd>

                    <dt class="col-sm-3">Ultimo Sync</dt>
                    <dd class="col-sm-9">{{ $invoice->asaas_last_sync_at ? $invoice->asaas_last_sync_at->format('d/m/Y H:i') : '-' }}
                    </dd>

                    <dt class="col-sm-3">Ultimo Erro</dt>
                    <dd class="col-sm-9 text-danger">{{ $invoice->asaas_last_error ?? '-' }}</dd>

                    <dt class="col-sm-3">Link de Pagamento</dt>
                    <dd class="col-sm-9">
                        @if ($invoice->payment_link)
                            <a href="{{ $invoice->payment_link }}" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1 text-primary"></i>
                                {{ $invoice->payment_link }}
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2 copy-link-btn"
                                data-link="{{ $invoice->payment_link }}">
                                <i class="fas fa-copy me-1"></i> Copiar link
                            </button>
                        @else
                            -
                        @endif
                    </dd>
                </dl>

                <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                    @if ($canShowSyncButton)
                        <form action="{{ route('Platform.invoices.sync', $invoice->id) }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-1"></i> Sincronizar com Asaas
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('Platform.invoices.refresh-asaas-status', $invoice->id) }}" method="POST"
                        class="m-0 p-0">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-search me-1"></i> Consultar status Asaas
                        </button>
                    </form>

                    <form action="{{ route('Platform.invoices.recreate-asaas-payment', $invoice->id) }}" method="POST"
                        class="m-0 p-0">
                        @csrf
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Recriar cobranca Asaas
                        </button>
                    </form>

                    <form action="{{ route('Platform.invoices.resend-payment-link', $invoice->id) }}" method="POST"
                        class="m-0 p-0">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fab fa-whatsapp me-1"></i> Reenviar link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {
            $(document).on('click', '.copy-link-btn', async function() {
                const link = $(this).data('link');
                if (!link) return;

                try {
                    await navigator.clipboard.writeText(link);
                } catch (e) {
                    const temp = document.createElement('textarea');
                    temp.value = link;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                }
            });
        });
    </script>
@endpush
