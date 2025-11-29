@extends('layouts.connect_plus.app')

@section('title', 'Sincronização de Calendário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Sincronização de Calendário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Sincronização de Calendário</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Estados de Sincronização</h4>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Agendamento</th>
                                    <th>ID Evento Externo</th>
                                    <th>Provedor</th>
                                    <th>Última Sincronização</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($syncStates as $syncState)
                                    <tr>
                                        <td>{{ $syncState->id }}</td>
                                        <td>{{ $syncState->appointment_id ?? 'N/A' }}</td>
                                        <td>{{ $syncState->external_event_id ?? 'N/A' }}</td>
                                        <td>{{ $syncState->provider ?? 'N/A' }}</td>
                                        <td>{{ $syncState->last_sync_at ? $syncState->last_sync_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('tenant.calendar-sync.show', $syncState->id) }}" class="btn btn-info btn-sm">Ver</a>
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

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#datatable-list').DataTable();
    });
</script>
@endpush

