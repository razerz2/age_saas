@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Editar Regra de Acesso</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.subscription-access.index') }}" class="text-muted">Regras de Acesso</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Editar</li>
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

                <h4 class="card-title mb-4">Editar Regra de Acesso</h4>
                <form method="POST" action="{{ route('Platform.subscription-access.update', $rule->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Plano</label>
                            <input type="text" class="form-control" value="{{ $rule->plan->name ?? '-' }}" disabled>
                            <small class="text-muted">O plano não pode ser alterado após a criação da regra.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Máximo de Usuários Admin <span class="text-danger">*</span></label>
                            <input type="number" name="max_admin_users" value="{{ old('max_admin_users', $rule->max_admin_users) }}"
                                class="form-control" min="0" required>
                            @error('max_admin_users')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Máximo de Usuários Comuns <span class="text-danger">*</span></label>
                            <input type="number" name="max_common_users" value="{{ old('max_common_users', $rule->max_common_users) }}"
                                class="form-control" min="0" required>
                            @error('max_common_users')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Máximo de Médicos <span class="text-danger">*</span></label>
                            <input type="number" name="max_doctors" value="{{ old('max_doctors', $rule->max_doctors) }}"
                                class="form-control" min="0" required>
                            @error('max_doctors')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Funcionalidades Disponíveis</label>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            @php
                                $allowedFeatures = $rule->features->where('pivot.allowed', true)->pluck('id')->toArray();
                            @endphp
                            @foreach ($features as $feature)
                                <div class="form-check mb-2">
                                    <input class="form-check-input feature-checkbox" type="checkbox" name="features[]"
                                        value="{{ $feature->id }}" id="feature_{{ $feature->id }}"
                                        @checked($feature->is_default || in_array($feature->id, old('features', $allowedFeatures)))
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
                            <i class="fa fa-save me-1"></i> Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('layouts.freedash.footer')
@endsection

