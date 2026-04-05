@extends('layouts.freedash.app')
@section('title', 'WhatsApp Não Oficial - Templates Padrão Tenant')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">WhatsApp Não Oficial - Templates Padrão Tenant</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Templates Padrão Tenant</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
            Baseline global para templates operacionais do Tenant no domínio WhatsApp Não Oficial.
            Estes registros podem ser copiados para <code>tenant.notification_templates</code> no provisionamento.
            Keys baseline: <code>appointment.pending_confirmation</code>, <code>appointment.confirmed</code>,
            <code>appointment.canceled</code>, <code>appointment.expired</code>, <code>waitlist.joined</code>,
            <code>waitlist.offered</code>.
        </div>
        <div class="alert alert-secondary">
            Distinção visual:
            <strong>Templates Internos Platform</strong> cobrem eventos SaaS da Platform;
            <strong>Templates Padrão Tenant</strong> cobrem eventos clínicos operacionais do tenant.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('Platform.tenant-default-notification-templates.index') }}" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="key" class="form-control" placeholder="Filtrar por key"
                            value="{{ $filters['key'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="channel" class="form-control">
                            <option value="">Todos os canais</option>
                            <option value="whatsapp" {{ ($filters['channel'] ?? '') === 'whatsapp' ? 'selected' : '' }}>whatsapp</option>
                            <option value="email" {{ ($filters['channel'] ?? '') === 'email' ? 'selected' : '' }}>email</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="category" class="form-control" placeholder="Filtrar por categoria"
                            value="{{ $filters['category'] ?? '' }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('Platform.tenant-default-notification-templates.index') }}" class="btn btn-outline-secondary w-100">Limpar</a>
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
                                <th>Título</th>
                                <th>Canal</th>
                                <th>Categoria</th>
                                <th>Idioma</th>
                                <th>Ativo</th>
                                <th>Atualizado</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->key }}</td>
                                    <td>{{ $template->title }}</td>
                                    <td>{{ $template->channel }}</td>
                                    <td>{{ $template->category }}</td>
                                    <td>{{ $template->language }}</td>
                                    <td>
                                        <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? 'ativo' : 'inativo' }}
                                        </span>
                                    </td>
                                    <td>{{ $template->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-warning text-white" title="Editar"
                                            href="{{ route('Platform.tenant-default-notification-templates.edit', $template) }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Nenhum template encontrado.</td>
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
