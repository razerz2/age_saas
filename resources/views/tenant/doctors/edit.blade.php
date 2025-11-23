@extends('layouts.connect_plus.app')

@section('title', 'Editar Médico')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Médico </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.doctors.index') }}">Médicos</a>
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

                    <form method="POST" action="{{ route('tenant.doctors.update', $doctor->id) }}" class="forms-sample">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="user_id">Usuário *</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">Selecione um usuário</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $doctor->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name_full }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="crm_number">Número do CRM</label>
                            <input id="crm_number" type="text" name="crm_number" class="form-control"
                                value="{{ $doctor->crm_number }}" placeholder="Digite o número do CRM">
                        </div>

                        <div class="form-group mb-3">
                            <label for="crm_state">Estado do CRM</label>
                            <input id="crm_state" type="text" name="crm_state" class="form-control" maxlength="2"
                                value="{{ $doctor->crm_state }}" placeholder="Ex: SP">
                        </div>

                        <div class="form-group mb-3">
                            <label for="signature">Assinatura</label>
                            <input id="signature" type="text" name="signature" class="form-control"
                                value="{{ $doctor->signature }}" placeholder="Digite a assinatura">
                        </div>

                        <div class="form-group mb-3">
                            <label>Especialidades</label>
                            <div class="d-flex flex-wrap gap-3">
                                @php
                                    $doctorSpecialties = $doctor->specialties->pluck('id')->toArray();
                                @endphp
                                @foreach ($specialties as $specialty)
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="specialties[]"
                                                value="{{ $specialty->id }}"
                                                {{ in_array($specialty->id, $doctorSpecialties) ? 'checked' : '' }}>
                                            {{ $specialty->name }}
                                            <i class="input-helper"></i>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Salvar</button>
                            <a href="{{ route('tenant.doctors.index') }}" class="btn btn-light btn-lg">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

