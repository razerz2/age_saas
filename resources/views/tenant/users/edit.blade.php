@extends('layouts.connect_plus.app')

@section('title', 'Editar Usuário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Usuário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.users.index') }}">Usuários</a>
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

                    <form method="POST" action="{{ route('tenant.users.update', $user) }}" class="forms-sample">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="name_full">Nome Completo</label>
                            <input id="name_full" type="text" name="name_full" class="form-control"
                                value="{{ $user->name_full }}" placeholder="Digite o nome completo" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">Nome de Exibição</label>
                            <input id="name" type="text" name="name" class="form-control"
                                value="{{ $user->name }}" placeholder="Digite o nome de exibição" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">E-mail</label>
                            <input id="email" type="email" name="email" class="form-control"
                                value="{{ $user->email }}" placeholder="Digite o e-mail" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Ativo</option>
                                <option value="blocked" {{ $user->status === 'blocked' ? 'selected' : '' }}>Bloqueado
                                </option>
                            </select>
                        </div>

                        <!-- Card de Módulos -->
                        <div class="form-group mb-3">
                            <br>
                            <label class="form-label">Módulos</label>
                            <br>
                            <p class="text-muted small">Selecione os módulos disponíveis:</p>
                            @php
                                $modules = App\Models\Tenant\Module::all();
                                $userModules = is_array($user->modules) ? $user->modules : (json_decode($user->modules, true) ?: []);
                            @endphp
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($modules as $module)
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" 
                                                class="form-check-input" 
                                                name="modules[]"
                                                value="{{ $module['key'] }}" 
                                                {{ in_array($module['key'], $userModules) ? 'checked' : '' }}>
                                            {{ $module['name'] }}
                                            <i class="input-helper"></i>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Salvar</button>
                            <a href="{{ route('tenant.users.index') }}" class="btn btn-light btn-lg">Cancelar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
