@extends('layouts.freedash.app')
@section('content')

<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                Notificações do Sistema
            </h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">
                            Notificações do Sistema
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Lista de Notificações</h4>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="system_notifications_table" class="table table-striped table-bordered text-nowrap align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Título</th>
                                    <th>Contexto</th>
                                    <th>Nível</th>
                                    <th>Status</th>
                                    <th>Criada em</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notifications as $notification)
                                    <tr class="{{ $notification->status === 'new' ? 'table-light' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $notification->title }}</td>
                                        <td>{{ ucfirst($notification->context ?? '-') }}</td>
                                        <td>
                                            <span class="badge 
                                                @if ($notification->level == 'error') bg-danger
                                                @elseif ($notification->level == 'warning') bg-warning
                                                @else bg-info text-dark @endif">
                                                {{ ucfirst($notification->level) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if ($notification->status == 'read') bg-success
                                                @else bg-primary @endif">
                                                {{ $notification->status == 'read' ? 'Lida' : 'Nova' }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('Platform.system_notifications.show', $notification->id) }}"
                                               class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
            $('#system_notifications_table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[5, 'desc']],
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush
