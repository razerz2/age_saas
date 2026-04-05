@extends('layouts.freedash.app')
@section('title', 'WhatsApp Oficial - Templates Oficiais Tenant')

@php
    $statusBadge = [
        'approved' => 'success',
        'pending' => 'warning',
        'draft' => 'secondary',
        'rejected' => 'danger',
        'archived' => 'dark',
    ];
@endphp

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">WhatsApp Oficial - Templates Oficiais Tenant</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Templates Oficiais Tenant</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                @can('manageBindings', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                    <a href="{{ route('Platform.whatsapp-official-tenant-templates.bindings.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-link me-1"></i> Vínculos Ativos
                    </a>
                @endcan
                @can('create', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                    <a href="{{ route('Platform.whatsapp-official-tenant-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Novo Template
                    </a>
                @endcan
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
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            Esta tela representa o baseline padrão oficial tenant. Não é catálogo global genérico e não é mapeamento tenant-aware.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('Platform.whatsapp-official-tenant-templates.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="key" class="form-control" placeholder="Filtrar por key"
                            value="{{ $filters['key'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">Todos os status</option>
                            @foreach($statusOptions as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>
                                    {{ strtoupper($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="language" class="form-control">
                            <option value="">Todos os idiomas</option>
                            @foreach($languageOptions as $language)
                                <option value="{{ $language }}" {{ ($filters['language'] ?? '') === $language ? 'selected' : '' }}>
                                    {{ $language }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('Platform.whatsapp-official-tenant-templates.index') }}" class="btn btn-outline-secondary w-100">Limpar</a>
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
                                <th>Key</th>
                                <th>Evento Tenant</th>
                                <th>Nome Meta</th>
                                <th>Idioma</th>
                                <th>Status</th>
                                <th>Versão</th>
                                <th>Última sincronização</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td><code>{{ $template->key }}</code></td>
                                    <td>{{ $eventLabels[$template->key] ?? '-' }}</td>
                                    <td>{{ $template->meta_template_name }}</td>
                                    <td>{{ $template->language }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadge[(string) $template->status] ?? 'secondary' }}">
                                            {{ strtoupper((string) $template->status) }}
                                        </span>
                                    </td>
                                    <td>v{{ (int) $template->version }}</td>
                                    <td>{{ $template->last_synced_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('Platform.whatsapp-official-tenant-templates.show', $template) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('syncStatus', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                                            <form action="{{ route('Platform.whatsapp-official-tenant-templates.sync', $template) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Sincronizar status na Meta">
                                                    <i class="fas fa-rotate"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @can('update', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                                            <a href="{{ route('Platform.whatsapp-official-tenant-templates.edit', $template) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Nenhum template padrão oficial tenant encontrado.</td>
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
