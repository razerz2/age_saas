@extends('layouts.freedash.app')
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
                <h4 class="card-title mb-3">Lista de Faturas</h4>
                <div class="table-responsive">
                    <table id="invoices_table" class="table table-striped table-bordered text-nowrap align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tenant</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
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
                                    <td class="text-center">
                                        <a title="Visualizar" href="{{ route('Platform.invoices.show', $invoice->id) }}"
                                            class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                        <a title="Editar" href="{{ route('Platform.invoices.edit', $invoice->id) }}"
                                            class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                        @if (in_array($invoice->asaas_sync_status, ['failed', 'pending']))
                                            <form action="{{ route('Platform.invoices.sync', $invoice->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning"
                                                    title="Tentar sincronizar novamente com Asaas">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('Platform.invoices.destroy', $invoice->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button title="Exclusão" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Deseja realmente excluir esta fatura?')">
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
        });
    </script>
@endpush
