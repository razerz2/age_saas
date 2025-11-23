@extends('layouts.connect_plus.app')

@section('title', 'Editar Especialidade')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Especialidade </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.specialties.index') }}">Especialidades</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Editar Dados</h4>
                    <p class="card-description"> Atualize as informações abaixo </p>

                    <form method="POST" action="{{ route('tenant.specialties.update', $specialty->id) }}" class="forms-sample">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="name">Nome *</label>
                            <input id="name" type="text" name="name" class="form-control"
                                value="{{ $specialty->name }}" placeholder="Digite o nome" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="code">Código</label>
                            <input id="code" type="text" name="code" class="form-control"
                                value="{{ $specialty->code }}" placeholder="Digite o código">
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Salvar</button>
                            <a href="{{ route('tenant.specialties.index') }}" class="btn btn-light btn-lg">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

