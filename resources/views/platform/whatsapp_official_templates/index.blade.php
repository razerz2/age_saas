@extends('layouts.freedash.app')
@section('title', 'Templates WhatsApp Oficial')

@php
    $badgeMap = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'archived' => 'dark',
    ];
    $eventDescriptions = [
        'invoice.created' => 'Fatura criada',
        'invoice.upcoming_due' => 'Lembrete de vencimento',
        'invoice.overdue' => 'Fatura vencida',
        'tenant.suspended_due_to_overdue' => 'Suspensão por inadimplência',
        'security.2fa_code' => 'Código de verificação (2FA)',
        'tenant.welcome' => 'Boas-vindas ao tenant',
        'subscription.created' => 'Assinatura criada',
        'subscription.recovery_started' => 'Recovery de assinatura iniciado',
        'credentials.resent' => 'Reenvio de credenciais',
    ];
@endphp

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Templates WhatsApp Oficial</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Templates WhatsApp Oficial</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end d-flex gap-2">
                    <a href="{{ route('Platform.whatsapp-official-templates.bindings.index') }}" class="btn btn-outline-primary shadow-sm">
                        <i class="fas fa-link me-1"></i> Vínculos Ativos
                    </a>
                    <a href="{{ route('Platform.whatsapp-official-templates.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fa fa-plus me-1"></i> Novo Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="alert alert-info">
            Este módulo gerencia apenas templates oficiais da Platform para a Meta Cloud API.
            Templates operacionais de clínica (appointment.*, waitlist.*) pertencem ao Tenant em Settings > Editor.
        </div>
        <div class="alert alert-warning">
            Envio oficial de WhatsApp só ocorre quando o template estiver em <strong>APPROVED</strong> e o provider ativo for
            <code>whatsapp_business</code>. Templates em <strong>DRAFT</strong>, <strong>PENDING</strong> ou
            <strong>REJECTED</strong> não são usados no runtime.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('Platform.whatsapp-official-templates.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="key" class="form-control" placeholder="Filtrar por key"
                            value="{{ $filters['key'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">Todos os status</option>
                            @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}" {{ ($filters['status'] ?? '') === $statusOption ? 'selected' : '' }}>
                                    {{ strtoupper($statusOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('Platform.whatsapp-official-templates.index') }}" class="btn btn-outline-secondary w-100">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Key interna</th>
                                <th>Evento SaaS</th>
                                <th>Nome Meta</th>
                                <th>Categoria</th>
                                <th>Idioma</th>
                                <th>Versão</th>
                                <th>Status</th>
                                <th>Última sincronização</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->key }}</td>
                                    <td>{{ $eventDescriptions[$template->key] ?? '-' }}</td>
                                    <td>{{ $template->meta_template_name }}</td>
                                    <td>{{ $template->category }}</td>
                                    <td>{{ $template->language }}</td>
                                    <td>{{ $template->version }}</td>
                                    <td>
                                        <span class="badge bg-{{ $badgeMap[$template->status] ?? 'secondary' }}">
                                            {{ strtoupper($template->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $template->last_synced_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-info text-white" title="Visualizar"
                                            href="{{ route('Platform.whatsapp-official-templates.show', $template) }}">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($template->isDirectlyEditable())
                                            <a class="btn btn-sm btn-warning text-white" title="Editar template"
                                                href="{{ route('Platform.whatsapp-official-templates.edit', $template) }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('Platform.whatsapp-official-templates.duplicate', $template) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Duplicar versão">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Nenhum template encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
