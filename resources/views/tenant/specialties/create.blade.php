@extends('layouts.connect_plus.app')

@section('title', 'Criar Especialidade')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Especialidade </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.specialties.index') }}">Especialidades</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Nova Especialidade</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form method="POST" action="{{ route('tenant.specialties.store') }}" class="forms-sample">
                        @csrf

                        <div class="form-group">
                            <label>Nome *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Código</label>
                            <input type="text" name="code" class="form-control" placeholder="Digite o código">
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('tenant.specialties.index') }}" class="btn btn-light">Cancelar</a>

                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection

