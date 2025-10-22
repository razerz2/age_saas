@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Editar Especialidade Médica</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.medical_specialties_catalog.index') }}"
                                    class="text-muted">Especialidades</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.medical_specialties_catalog.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Editar Especialidade</h4>

                        <form method="POST"
                            action="{{ route('Platform.medical_specialties_catalog.update', $medicalSpecialtyCatalog->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nome da Especialidade</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $medicalSpecialtyCatalog->name) }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Código CBO (opcional)</label>
                                    <input type="text" name="code" class="form-control"
                                        value="{{ old('code', $medicalSpecialtyCatalog->code) }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo</label>
                                    <select name="type" class="form-select" required>
                                        <option value="medical_specialty"
                                            {{ old('type', $medicalSpecialtyCatalog->type ?? '') == 'medical_specialty' ? 'selected' : '' }}>
                                            Especialidade Médica
                                        </option>
                                        <option value="health_profession"
                                            {{ old('type', $medicalSpecialtyCatalog->type ?? '') == 'health_profession' ? 'selected' : '' }}>
                                            Profissão da Saúde
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fa fa-save me-1"></i> Salvar Alterações
                                </button>
                                <a href="{{ route('Platform.medical_specialties_catalog.index') }}"
                                    class="btn btn-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
