@extends('layouts.freedash.app')
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Faturas</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
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
                                    <span class="badge
                                        @if($invoice->status == 'paid') bg-success
                                        @elseif($invoice->status == 'overdue') bg-danger
                                        @elseif($invoice->status == 'pending') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('Platform.invoices.show', $invoice->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('Platform.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('Platform.invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir esta fatura?')">
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
