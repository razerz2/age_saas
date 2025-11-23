@extends('layouts.connect_plus.app')

@section('title', 'Criar Médico')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Médico </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.doctors.index') }}">Médicos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Novo Médico</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form method="POST" action="{{ route('tenant.doctors.store') }}" class="forms-sample">
                        @csrf

                        <div class="form-group">
                            <label>Usuário *</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">Selecione um usuário</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name_full }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Número do CRM</label>
                            <input type="text" name="crm_number" class="form-control" placeholder="Digite o número do CRM">
                        </div>

                        <div class="form-group">
                            <label>Estado do CRM</label>
                            <input type="text" name="crm_state" class="form-control" maxlength="2" placeholder="Ex: SP">
                        </div>

                        <div class="form-group">
                            <label>Assinatura</label>
                            <input type="text" name="signature" class="form-control" placeholder="Digite a assinatura">
                        </div>

                        <div class="form-group">
                            <label>Especialidades</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($specialties as $specialty)
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="specialties[]"
                                                value="{{ $specialty->id }}">
                                            {{ $specialty->name }}
                                            <i class="input-helper"></i>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('tenant.doctors.index') }}" class="btn btn-light">Cancelar</a>

                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection

