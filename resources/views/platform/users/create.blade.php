@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    {{ isset($user) ? 'Editar Usuário' : 'Novo Usuário' }}
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.users.index') }}"
                                    class="text-muted">Usuários</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">
                                {{ isset($user) ? 'Editar' : 'Novo' }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.users.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                         {{-- 🔹 Exibição de erros de validação --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                <strong>Ops!</strong> Verifique os erros abaixo:
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fechar"></button>
                            </div>
                        @endif
                        
                        <form method="POST"
                            action="{{ isset($user) ? route('Platform.users.update', $user->id) : route('Platform.users.store') }}">
                            @csrf
                            @if (isset($user))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $user->name ?? '') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $user->email ?? '') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Senha {{ isset($user) ? '(opcional)' : '' }}</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirmar Senha</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>

                            {{-- 🔹 Seleção de Módulos --}}
                            <div class="border-top pt-3 mt-4">
                                <h5 class="mb-3 fw-semibold text-dark">
                                    <i class="fa fa-layer-group text-primary me-2"></i>
                                    Módulos Permitidos
                                </h5>
                                <div class="row">
                                    @php($modules = \App\Models\Platform\Module::all())

                                    @foreach ($modules as $module)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="modules[]"
                                                    value="{{ $module['key'] }}"
                                                    {{ in_array($module['key'], old('modules', $user->modules ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    <i
                                                        class="fa {{ $module['icon'] ?? 'fa-circle' }} text-primary me-1"></i>
                                                    {{ $module['name'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fa fa-save me-1"></i>
                                    {{ isset($user) ? 'Salvar Alterações' : 'Criar Usuário' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
