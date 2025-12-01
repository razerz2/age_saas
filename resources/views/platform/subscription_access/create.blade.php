@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Nova Regra de Acesso</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.subscription-access.index') }}" class="text-muted">Regras de Acesso</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Nova</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.subscription-access.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Ops!</strong> Verifique os erros abaixo:
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                <h4 class="card-title mb-4">Cadastrar Regra de Acesso</h4>
                <form method="POST" action="{{ route('Platform.subscription-access.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Plano <span class="text-danger">*</span></label>
                            <select name="plan_id" class="form-select" required>
                                <option value="">Selecione um plano...</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Máximo de Usuários Admin <span class="text-danger">*</span></label>
                            <input type="number" name="max_admin_users" value="{{ old('max_admin_users', 0) }}"
                                class="form-control" min="0" required>
                            @error('max_admin_users')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Máximo de Usuários Comuns <span class="text-danger">*</span></label>
                            <input type="number" name="max_common_users" value="{{ old('max_common_users', 0) }}"
                                class="form-control" min="0" required>
                            @error('max_common_users')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Máximo de Médicos <span class="text-danger">*</span></label>
                            <input type="number" name="max_doctors" value="{{ old('max_doctors', 0) }}"
                                class="form-control" min="0" required>
                            @error('max_doctors')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Funcionalidades Disponíveis</label>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            @foreach ($features as $feature)
                                <div class="form-check mb-2">
                                    <input class="form-check-input feature-checkbox" type="checkbox" name="features[]"
                                        value="{{ $feature->id }}" id="feature_{{ $feature->id }}"
                                        @checked($feature->is_default || in_array($feature->id, old('features', [])))
                                        @disabled($feature->is_default)>
                                    <label class="form-check-label" for="feature_{{ $feature->id }}">
                                        {{ $feature->label }}
                                        @if ($feature->is_default)
                                            <span class="badge bg-success ms-2">Essencial</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Funcionalidades marcadas como "Essencial" são obrigatórias e não podem ser desmarcadas.
                        </small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa fa-save me-1"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('layouts.freedash.footer')
@endsection

