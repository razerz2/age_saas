@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    {{ isset($notificationOutbox) ? 'Editar Notificação' : 'Nova Notificação' }}
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.notifications_outbox.index') }}"
                                    class="text-muted">Notificações</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">
                                {{ isset($notificationOutbox) ? 'Editar' : 'Nova' }}
                            </li>
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
                        <h4 class="card-title mb-4">
                            {{ isset($notificationOutbox) ? 'Editar Notificação' : 'Cadastrar Notificação' }}
                        </h4>

                        <form method="POST"
                            action="{{ isset($notificationOutbox)
                                ? route('Platform.notifications_outbox.update', $notificationOutbox)
                                : route('Platform.notifications_outbox.store') }}">
                            @csrf
                            @if (isset($notificationOutbox))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tenant (opcional)</label>
                                    <select name="tenant_id" class="form-select">
                                        <option value="">— Nenhum —</option>
                                        @foreach ($tenants as $tenant)
                                            <option value="{{ $tenant->id }}"
                                                {{ old('tenant_id', $notificationOutbox->tenant_id ?? '') == $tenant->id ? 'selected' : '' }}>
                                                {{ $tenant->trade_name ?? $tenant->legal_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Canal</label>
                                    <select name="channel" class="form-select" required>
                                        @foreach (['email' => 'E-mail', 'whatsapp' => 'WhatsApp', 'sms' => 'SMS', 'inapp' => 'In-App'] as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ old('channel', $notificationOutbox->channel ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Assunto</label>
                                    <input type="text" name="subject" class="form-control"
                                        value="{{ old('subject', $notificationOutbox->subject ?? '') }}">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Corpo da Mensagem</label>
                                    <textarea name="body" rows="5" class="form-control" required>{{ old('body', $notificationOutbox->body ?? '') }}</textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Agendar Envio</label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control"
                                        value="{{ old('scheduled_at', isset($notificationOutbox->scheduled_at) ? $notificationOutbox->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="queued"
                                            {{ old('status', $notificationOutbox->status ?? '') == 'queued' ? 'selected' : '' }}>
                                            Fila</option>
                                        <option value="sent"
                                            {{ old('status', $notificationOutbox->status ?? '') == 'sent' ? 'selected' : '' }}>
                                            Enviado</option>
                                        <option value="error"
                                            {{ old('status', $notificationOutbox->status ?? '') == 'error' ? 'selected' : '' }}>
                                            Erro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fa fa-save me-1"></i>
                                    {{ isset($notificationOutbox) ? 'Salvar Alterações' : 'Criar Notificação' }}
                                </button>
                                <a href="{{ route('Platform.notifications_outbox.index') }}"
                                    class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
