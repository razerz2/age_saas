@extends('layouts.freedash.app')
@section('title', 'WhatsApp Não Oficial - Templates Internos Platform')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">WhatsApp Não Oficial - Templates Internos Platform</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Templates Internos Platform</li>
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
            Catálogo interno da Platform para mensagens de <strong>WhatsApp Não Oficial</strong>.
            Sem cadastro/aprovação na Meta. O template é renderizado internamente e enviado pelo provider não oficial ativo.
            Baseline inicial: <code>invoice.created</code>, <code>invoice.upcoming_due</code>, <code>invoice.overdue</code>,
            <code>tenant.suspended_due_to_overdue</code>, <code>tenant.welcome</code>, <code>subscription.created</code>,
            <code>subscription.recovery_started</code>, <code>credentials.resent</code> e <code>security.2fa_code</code>.
        </div>
        <div class="alert alert-secondary">
            Distinção de domínio:
            <strong>WhatsApp Oficial</strong> usa templates Meta aprovados;
            <strong>WhatsApp Não Oficial</strong> usa templates internos operacionais.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('Platform.whatsapp-unofficial-templates.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="key" class="form-control" placeholder="Filtrar por key"
                            value="{{ $filters['key'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="category" class="form-control" placeholder="Filtrar por categoria"
                            value="{{ $filters['category'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <select name="is_active" class="form-control">
                            <option value="">Todos os status</option>
                            <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('Platform.whatsapp-unofficial-templates.index') }}" class="btn btn-outline-secondary w-100">Limpar</a>
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
                                <th>Nome/Título</th>
                                <th>Categoria</th>
                                <th>Variáveis</th>
                                <th>Status</th>
                                <th>Atualizado</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->key }}</td>
                                    <td>{{ $template->title }}</td>
                                    <td>{{ $template->category }}</td>
                                    <td>{{ is_array($template->variables) ? count($template->variables) : 0 }}</td>
                                    <td>
                                        <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? 'ativo' : 'inativo' }}
                                        </span>
                                    </td>
                                    <td>{{ $template->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-info text-white" title="Visualizar"
                                            href="{{ route('Platform.whatsapp-unofficial-templates.show', $template) }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a class="btn btn-sm btn-warning text-white" title="Editar"
                                            href="{{ route('Platform.whatsapp-unofficial-templates.edit', $template) }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('Platform.whatsapp-unofficial-templates.toggle', $template) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $template->is_active ? 'secondary' : 'success' }}"
                                                title="{{ $template->is_active ? 'Inativar' : 'Ativar' }}">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Nenhum template encontrado.</td>
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
