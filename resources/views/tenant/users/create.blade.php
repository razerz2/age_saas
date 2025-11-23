@extends('layouts.connect_plus.app')

@section('title', 'Criar Usuário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Usuário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.users.index') }}">Usuários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Novo Usuário</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form method="POST" action="{{ route('tenant.users.store') }}" class="forms-sample">
                        @csrf

                        <div class="form-group">
                            <label>Nome Completo</label>
                            <input type="text" name="name_full" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Nome de Exibição</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Ativo</option>
                                <option value="blocked">Bloqueado</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('tenant.users.index') }}" class="btn btn-light">Cancelar</a>

                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection
