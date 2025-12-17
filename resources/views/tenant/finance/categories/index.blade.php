@extends('layouts.connect_plus.app')

@section('title', 'Categorias Financeiras')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-tag-multiple text-primary me-2"></i>
            Categorias Financeiras
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Categorias</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Categorias</h4>

                    <a href="{{ workspace_route('tenant.finance.categories.create') }}" class="btn btn-primary mb-3">
                        <i class="mdi mdi-plus"></i> Nova Categoria
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Cor</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            @if($category->type === 'income')
                                                <span class="badge bg-success">Receita</span>
                                            @else
                                                <span class="badge bg-danger">Despesa</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $category->color ?? '#3b82f6' }};">
                                                {{ $category->color ?? '#3b82f6' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($category->active)
                                                <span class="badge bg-success">Ativa</span>
                                            @else
                                                <span class="badge bg-secondary">Inativa</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.finance.categories.show', ['slug' => tenant()->subdomain, 'category' => $category->id]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ workspace_route('tenant.finance.categories.edit', ['slug' => tenant()->subdomain, 'category' => $category->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ workspace_route('tenant.finance.categories.destroy', ['slug' => tenant()->subdomain, 'category' => $category->id]) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhuma categoria cadastrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

