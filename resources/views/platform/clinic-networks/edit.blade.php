@extends('layouts.freedash.app')
@section('title', 'Editar Clinic Networks')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Editar Rede de Clínicas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.clinic-networks.index') }}" class="text-muted">Redes de Clínicas</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">{{ $network->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.clinic-networks.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Editar Rede: {{ $network->name }}</h4>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Erro!</strong> Por favor, corrija os seguintes erros:
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('Platform.clinic-networks.update', $network->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nome da Rede <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $network->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug (Subdomínio) <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('slug') is-invalid @enderror" 
                                               id="slug" 
                                               name="slug" 
                                               value="{{ old('slug', $network->slug) }}" 
                                               required
                                               pattern="[a-z0-9-]+">
                                        <small class="form-text text-muted">
                                            Apenas letras minúsculas, números e hífens. Usado no subdomínio
                                        </small>
                                        @error('slug')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', $network->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Rede ativa
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Redes inativas não aparecem na página pública
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('Platform.clinic-networks.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gestão de Tenants --}}
        @include('platform.clinic-networks._tenants', ['network' => $network, 'availableTenants' => $availableTenants])

        {{-- Gestão de Usuários da Rede --}}
        @include('platform.clinic-networks._users', ['network' => $network])
    </div>

@include("layouts.freedash.footer")
@endsection

