@extends('layouts.connect_plus.app')

@section('title', 'Calendários')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Calendários </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Calendários</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Calendários</h4>

                    <a href="{{ workspace_route('tenant.calendars.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Médico</th>
                                    <th>ID Externo</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($calendars as $calendar)
                                    <tr>
                                        <td>{{ $calendar->id }}</td>
                                        <td>{{ $calendar->name }}</td>
                                        <td>{{ $calendar->doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $calendar->external_id ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.calendars.show', $calendar->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            <a href="{{ workspace_route('tenant.calendars.edit', $calendar->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                            @php
                                                $user = auth('tenant')->user();
                                            @endphp
                                            @if ($user && $user->is_doctor)
                                                <a href="{{ workspace_route('tenant.calendars.events', ['id' => $calendar->id]) }}" class="btn btn-primary btn-sm">Eventos</a>
                                            @endif
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
        $('#datatable-list').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            }
        });
    });
</script>
@endpush

