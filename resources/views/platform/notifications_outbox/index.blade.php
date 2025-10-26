@extends('layouts.freedash.app')
@section('content')

<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Notificações Outbox</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">Notificações</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-5 align-self-center">
            <div class="customize-input float-end">
                <a href="{{ route('Platform.notifications_outbox.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fa fa-plus me-1"></i> Nova Notificação
                </a>
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
                        <table id="notifications_table" class="table table-striped table-bordered text-nowrap align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tenant</th>
                                    <th>Canal</th>
                                    <th>Assunto</th>
                                    <th>Status</th>
                                    <th>Agendada</th>
                                    <th>Enviada</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notifications as $notification)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $notification->tenant?->trade_name ?? '-' }}</td>
                                        <td><span class="badge bg-info">{{ strtoupper($notification->channel) }}</span></td>
                                        <td>{{ $notification->subject ?? '-' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if ($notification->status == 'sent') bg-success
                                                @elseif($notification->status == 'error') bg-danger
                                                @else bg-warning @endif">
                                                {{ ucfirst($notification->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $notification->scheduled_at ? $notification->scheduled_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $notification->sent_at ? $notification->sent_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="text-center">
                                            <a title="Visualizar" href="{{ route('platform.notifications_outbox.show', $notification->id) }}"
                                                class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a title="Editar" href="{{ route('platform.notifications_outbox.edit', $notification->id) }}"
                                                class="btn btn-sm btn-warning text-white">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('platform.notifications_outbox.destroy', $notification->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button title="Exclusão" type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Deseja realmente excluir esta notificação?')">
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
    </div>
</div>

@include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#notifications_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush