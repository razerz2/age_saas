@extends('layouts.connect_plus.app')

@section('title', 'Gerenciar Permissões de Médicos')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Gerenciar Permissões de Médicos </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.users.index') }}">Usuários</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.users.show', $user->id) }}">{{ $user->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Permissões de Médicos</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Permissões de Médicos - {{ $user->name_full }}
                    </h4>
                    <p class="card-description">
                        Selecione os médicos que este usuário pode visualizar nas agendas.
                        <br>
                        <strong>Nota:</strong> Se nenhum médico for selecionado, o usuário poderá visualizar todos os médicos.
                    </p>

                    <form action="{{ workspace_route('tenant.users.doctor-permissions.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach ($doctors as $doctor)
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="doctor_ids[]" 
                                            value="{{ $doctor->id }}" 
                                            id="doctor_{{ $doctor->id }}"
                                            {{ in_array($doctor->id, $userPermissions) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="doctor_{{ $doctor->id }}">
                                            <strong>{{ $doctor->user->name_full ?? 'Sem nome' }}</strong>
                                            @if ($doctor->crm_number)
                                                <br>
                                                <small class="text-muted">CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }}</small>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($doctors->isEmpty())
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i>
                                Nenhum médico cadastrado no sistema.
                            </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Salvar Permissões
                            </button>
                            <a href="{{ workspace_route('tenant.users.show', $user->id) }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

