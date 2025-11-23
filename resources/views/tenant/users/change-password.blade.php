@extends('layouts.connect_plus.app')

@section('title', 'Alterar Senha')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Alterar Senha </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.users.index') }}">Usuários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Alterar Senha</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Alterar Senha</h4>
                    <p class="card-description">Digite a senha atual e a nova senha.</p>

                    <form method="POST" action="{{ route('tenant.users.change-password', $user->id) }}"
                        class="forms-sample">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="current_password">Senha Atual</label>
                            <input type="password" name="current_password" class="form-control" required>
                            @error('current_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password">Nova Senha</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password_confirmation">Confirmar Nova Senha</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Alterar Senha</button>
                            <a href="{{ route('tenant.users.index') }}" class="btn btn-light btn-lg">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
