@extends('layouts.freedash.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-key text-primary me-2"></i> Tokens de API - {{ $tenant->trade_name ?? $tenant->legal_name }}
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('Platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar ao Tenant
                            </a>
                            <a href="{{ route('Platform.tenants.api-tokens.create', $tenant) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Novo Token
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($tokens->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhum token de API criado para este tenant ainda.
                                <a href="{{ route('Platform.tenants.api-tokens.create', $tenant) }}" class="alert-link">
                                    Criar primeiro token
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Status</th>
                                            <th>Expira em</th>
                                            <th>Criado por</th>
                                            <th>Criado em</th>
                                            <th>Último uso</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tokens as $token)
                                            <tr>
                                                <td>{{ $token->name }}</td>
                                                <td>
                                                    @if ($token->active)
                                                        <span class="badge bg-success">Ativo</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($token->expires_at)
                                                        @if ($token->expires_at->isPast())
                                                            <span class="text-danger">Expirado</span>
                                                        @else
                                                            {{ $token->expires_at->format('d/m/Y H:i') }}
                                                        @endif
                                                    @else
                                                        <span class="text-muted">Sem expiração</span>
                                                    @endif
                                                </td>
                                                <td>{{ $token->creator->name ?? 'N/A' }}</td>
                                                <td>{{ $token->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @if ($token->last_used_at)
                                                        {{ $token->last_used_at->format('d/m/Y H:i') }}
                                                    @else
                                                        <span class="text-muted">Nunca usado</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('Platform.tenants.api-tokens.show', [$tenant, $token]) }}" 
                                                       class="btn btn-sm btn-info" title="Ver Token">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('Platform.tenants.api-tokens.edit', [$tenant, $token]) }}" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('Platform.tenants.api-tokens.destroy', [$tenant, $token]) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirmSubmit(event, 'Tem certeza que deseja excluir este token? Esta ação não pode ser desfeita.', 'Confirmar Exclusão')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

