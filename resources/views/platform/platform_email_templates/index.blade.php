@extends('layouts.freedash.app')
@section('title', 'Templates de Email Platform')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Templates de Email Platform</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Email Platform</li>
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
            Catálogo de Templates de E-mail da Platform. Este módulo não inclui layout estrutural de e-mail.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('Platform.platform-email-templates.index') }}" class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="name" class="form-control" placeholder="Filtrar por key/evento"
                               value="{{ $filters['name'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="enabled" class="form-control">
                            <option value="">Todos os status</option>
                            <option value="1" {{ ($filters['enabled'] ?? '') === '1' ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ ($filters['enabled'] ?? '') === '0' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('Platform.platform-email-templates.index') }}" class="btn btn-outline-secondary w-100">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="bg-light">
                        <tr>
                            <th>Key</th>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Atualizado em</th>
                            <th class="text-center">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td><code>{{ $template->name }}</code></td>
                                <td>{{ $template->display_name }}</td>
                                <td>
                                    @if($template->enabled)
                                        <span class="badge bg-success">ATIVO</span>
                                    @else
                                        <span class="badge bg-secondary">INATIVO</span>
                                    @endif
                                </td>
                                <td>{{ $template->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('Platform.platform-email-templates.show', $template) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('Platform.platform-email-templates.edit', $template) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('Platform.platform-email-templates.toggle', $template) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('Platform.platform-email-templates.restore', $template) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum template de email platform encontrado.</td>
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
