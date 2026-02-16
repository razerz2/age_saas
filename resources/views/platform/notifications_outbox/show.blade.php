@extends('layouts.freedash.app')
@section('title', 'Visualizar Notifications Outbox')

@section('content')

<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes da Notificação</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.notifications_outbox.index') }}" class="text-muted">Notificações</a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">Visualizar</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-5 align-self-center">
            <div class="customize-input float-end">
                <a href="{{ route('Platform.notifications_outbox.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
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
                    <h4 class="card-title mb-4">Informações da Notificação</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 25%;">Tenant</th>
                                    <td>{{ $notificationOutbox->tenant?->trade_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Canal</th>
                                    <td><span class="badge bg-info">{{ strtoupper($notificationOutbox->channel) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Assunto</th>
                                    <td>{{ $notificationOutbox->subject ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Mensagem</th>
                                    <td style="white-space: pre-wrap;">{{ $notificationOutbox->body }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge 
                                            @if ($notificationOutbox->status == 'sent') bg-success
                                            @elseif($notificationOutbox->status == 'error') bg-danger
                                            @else bg-warning @endif">
                                            {{ ucfirst($notificationOutbox->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Agendada</th>
                                    <td>{{ $notificationOutbox->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Enviada</th>
                                    <td>{{ $notificationOutbox->sent_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Meta (JSON)</th>
                                    <td><pre>{{ json_encode($notificationOutbox->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <a href="{{ route('Platform.notifications_outbox.edit', $notificationOutbox->id) }}" 
                           class="btn btn-warning text-white shadow-sm">
                            <i class="fa fa-edit me-1"></i> Editar
                        </a>
                        <a href="{{ route('Platform.notifications_outbox.index') }}" class="btn btn-secondary">Voltar</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.freedash.footer')
@endsection
