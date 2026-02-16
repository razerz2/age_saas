@extends('layouts.freedash.app')
@section('title', 'Visualizar Subscription Access')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes da Regra de Acesso</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.subscription-access.index') }}" class="text-muted">Regras de Acesso</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Detalhes</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.subscription-access.index') }}" class="btn btn-secondary shadow-sm me-2">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
                </a>
                <a href="{{ route('Platform.subscription-access.edit', $rule->id) }}" class="btn btn-warning shadow-sm">
                    <i class="fa fa-edit me-1"></i> Editar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title mb-4">Informações da Regra de Acesso</h4>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Plano</label>
                        <p>{{ $rule->plan->name ?? '-' }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Criado em</label>
                        <p>{{ $rule->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Máximo de Usuários Admin</label>
                        <p class="fs-5">{{ $rule->max_admin_users }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Máximo de Usuários Comuns</label>
                        <p class="fs-5">{{ $rule->max_common_users }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Máximo de Médicos</label>
                        <p class="fs-5">{{ $rule->max_doctors }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Funcionalidades Permitidas</label>
                    <div class="border rounded p-3">
                        @php
                            $allowedFeatures = $rule->features->where('pivot.allowed', true);
                            $deniedFeatures = $rule->features->where('pivot.allowed', false);
                        @endphp

                        @if ($allowedFeatures->count() > 0)
                            <h6 class="text-success mb-2">
                                <i class="fa fa-check-circle"></i> Permitidas ({{ $allowedFeatures->count() }})
                            </h6>
                            <ul class="list-group mb-3">
                                @foreach ($allowedFeatures as $feature)
                                    <li class="list-group-item">
                                        <i class="fa fa-check text-success me-2"></i>
                                        {{ $feature->label }}
                                        @if ($feature->is_default)
                                            <span class="badge bg-success ms-2">Essencial</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($deniedFeatures->count() > 0)
                            <h6 class="text-danger mb-2">
                                <i class="fa fa-times-circle"></i> Negadas ({{ $deniedFeatures->count() }})
                            </h6>
                            <ul class="list-group">
                                @foreach ($deniedFeatures as $feature)
                                    <li class="list-group-item">
                                        <i class="fa fa-times text-danger me-2"></i>
                                        {{ $feature->label }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.freedash.footer')
@endsection

