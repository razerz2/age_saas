@extends('layouts.connect_plus.app')

@section('title', 'Criar Paciente')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Paciente </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.patients.index') }}">Pacientes</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Novo Paciente</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form method="POST" action="{{ route('tenant.patients.store') }}" class="forms-sample">
                        @csrf

                        <div class="form-group">
                            <label>Nome Completo *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>CPF *</label>
                            <input type="text" name="cpf" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Data de Nascimento</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active" class="form-control">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('tenant.patients.index') }}" class="btn btn-light">Cancelar</a>

                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection

