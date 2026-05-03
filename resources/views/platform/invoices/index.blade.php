@extends('layouts.freedash.app')
@section('title', 'Listar Invoices')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Faturas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Faturas</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.invoices.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fa fa-plus me-1"></i> Nova Fatura
                </a>
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

                <h4 class="card-title mb-3">Lista de Faturas</h4>
                <div class="table-responsive">
                    <table id="invoices_table" class="table table-striped table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tenant</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th>Metodo</th>
                                <th>Provider ID</th>
                                <th>Link</th>
                                <th>Sync Asaas</th>
                                <th>Ultimo Sync</th>
                                <th>Ultimo Erro</th>
                                <th class="text-center">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                @php
                                    $canShowSyncButton =
                                        in_array($invoice->asaas_sync_status, ['failed', 'pending'], true) ||
                                        (empty($invoice->provider_id) &&
                                            in_array($invoice->status, ['pending', 'overdue'], true));
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $invoice->tenant->trade_name ?? '-' }}</td>
                                    <td>{{ $invoice->formatted_amount }}</td>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="badge
                                            @if ($invoice->status == 'paid') bg-success
                                            @elseif($invoice->status == 'overdue') bg-danger
                                            @elseif($invoice->status == 'pending') bg-warning
                                            @else bg-secondary @endif">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $invoice->payment_method ?? '-' }}</td>
                                    <td>{{ $invoice->provider_id ?? '-' }}</td>
                                    <td>
                                        @if ($invoice->payment_link)
                                            <a href="{{ $invoice->payment_link }}" target="_blank">Abrir</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($invoice->asaas_sync_status ?? '-') }}</td>
                                    <td>{{ $invoice->asaas_last_sync_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-danger">{{ $invoice->asaas_last_error ?? '-' }}</td>
                                    <td class="text-center">
                                        <a title="Visualizar" href="{{ route('Platform.invoices.show', $invoice->id) }}"
                                            class="btn btn-sm btn-info mb-1"><i class="fa fa-eye"></i></a>
                                        <a title="Editar" href="{{ route('Platform.invoices.edit', $invoice->id) }}"
                                            class="btn btn-sm btn-warning mb-1"><i class="fa fa-edit"></i></a>

                                        @if ($canShowSyncButton)
                                            <form action="{{ route('Platform.invoices.sync', $invoice->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary mb-1"
                                                    title="Sincronizar com Asaas">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('Platform.invoices.refresh-asaas-status', $invoice->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary mb-1"
                                                title="Consultar status Asaas">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('Platform.invoices.recreate-asaas-payment', $invoice->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-dark mb-1"
                                                title="Recriar cobranca Asaas">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('Platform.invoices.resend-payment-link', $invoice->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success mb-1"
                                                title="Reenviar link de pagamento">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                        </form>

                                        @if ($invoice->payment_link)
                                            <button type="button" class="btn btn-sm btn-outline-secondary mb-1 copy-link-btn"
                                                data-link="{{ $invoice->payment_link }}" title="Copiar link">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        @endif

                                        <form action="{{ route('Platform.invoices.destroy', $invoice->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirmSubmit(event, 'Deseja realmente excluir esta fatura? Esta acao nao pode ser desfeita.', 'Confirmar Exclusao')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Exclusao" class="btn btn-sm btn-danger mb-1">
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

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#invoices_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });

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
