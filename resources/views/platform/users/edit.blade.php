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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Platform.users.index') }}" class="text-muted">Usuários</a></li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">{{ isset($user) ? 'Editar' : 'Novo' }}</li>
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
                    <form method="POST" 
                        action="{{ isset($user) ? route('Platform.users.update', $user->id) : route('Platform.users.store') }}">
                        @csrf
                        @if(isset($user)) @method('PUT') @endif

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

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fa fa-save me-1"></i> {{ isset($user) ? 'Salvar Alterações' : 'Criar Usuário' }}
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
