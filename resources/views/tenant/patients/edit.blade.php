@extends('layouts.connect_plus.app')

@section('title', 'Editar Paciente')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Paciente </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.patients.index') }}">Pacientes</a>
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

                    <form method="POST" action="{{ route('tenant.patients.update', $patient->id) }}" class="forms-sample">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="full_name">Nome Completo *</label>
                            <input id="full_name" type="text" name="full_name" class="form-control"
                                value="{{ $patient->full_name }}" placeholder="Digite o nome completo" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="cpf">CPF *</label>
                            <input id="cpf" type="text" name="cpf" class="form-control"
                                value="{{ $patient->cpf }}" placeholder="Digite o CPF" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="birth_date">Data de Nascimento</label>
                            <input id="birth_date" type="date" name="birth_date" class="form-control"
                                value="{{ $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '' }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">E-mail</label>
                            <input id="email" type="email" name="email" class="form-control"
                                value="{{ $patient->email }}" placeholder="Digite o e-mail">
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Telefone</label>
                            <input id="phone" type="text" name="phone" class="form-control"
                                value="{{ $patient->phone }}" placeholder="Digite o telefone">
                        </div>

                        <div class="form-group mb-3">
                            <label for="is_active">Status</label>
                            <select name="is_active" class="form-control">
                                <option value="1" {{ $patient->is_active ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ !$patient->is_active ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Salvar</button>
                            <a href="{{ route('tenant.patients.index') }}" class="btn btn-light btn-lg">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

