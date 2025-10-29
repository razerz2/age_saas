@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    {{ isset($medicalSpecialtyCatalog) ? 'Editar Especialidade' : 'Nova Especialidade' }}
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.medical_specialties_catalog.index') }}"
                                    class="text-muted">Especialidades</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">
                                {{ isset($medicalSpecialtyCatalog) ? 'Editar' : 'Nova' }}</li>
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
                        {{-- ✅ Alertas de sucesso --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- ⚠️ Alertas de aviso --}}
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- 🔹 Exibição de erros de validação --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                <strong>Ops!</strong> Verifique os erros abaixo:
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fechar"></button>
                            </div>
                        @endif
                        <h4 class="card-title mb-4">
                            {{ isset($medicalSpecialtyCatalog) ? 'Editar Especialidade' : 'Cadastrar Nova Especialidade' }}
                        </h4>

                        <form method="POST"
                            action="{{ isset($medicalSpecialtyCatalog)
                                ? route('Platform.medical_specialties_catalog.update', $medicalSpecialtyCatalog)
                                : route('Platform.medical_specialties_catalog.store') }}">
                            @csrf
                            @if (isset($medicalSpecialtyCatalog))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nome da Especialidade</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $medicalSpecialtyCatalog->name ?? '') }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Código CBO (opcional)</label>
                                    <input type="text" name="code" class="form-control"
                                        value="{{ old('code', $medicalSpecialtyCatalog->code ?? '') }}">
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
                                    <i class="fa fa-save me-1"></i>
                                    {{ isset($medicalSpecialtyCatalog) ? 'Salvar Alterações' : 'Criar Especialidade' }}
                                </button>
                                <a href="{{ route('Platform.medical_specialties_catalog.index') }}"
                                    class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
