@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Categoria')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-tag text-primary me-2"></i>
            Detalhes da Categoria
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.categories.index') }}">Categorias</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $category->name }}</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo:</strong>
                            @if($category->type === 'income')
                                <span class="badge bg-success">Receita</span>
                            @else
                                <span class="badge bg-danger">Despesa</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            @if($category->active)
                                <span class="badge bg-success">Ativa</span>
                            @else
                                <span class="badge bg-secondary">Inativa</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Cor:</strong>
                        <span class="badge" style="background-color: {{ $category->color ?? '#3b82f6' }};">
                            {{ $category->color ?? '#3b82f6' }}
                        </span>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ workspace_route('tenant.finance.categories.edit', ['slug' => tenant()->subdomain, 'category' => $category->id]) }}" 
                           class="btn btn-primary">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                        <a href="{{ workspace_route('tenant.finance.categories.index', ['slug' => tenant()->subdomain]) }}" 
                           class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

