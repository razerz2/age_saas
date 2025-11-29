@extends('layouts.connect_plus.app')

@section('title', 'Criar Calendário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Calendário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.calendars.index') }}">Calendários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-calendar-plus text-primary me-2"></i>
                                Novo Calendário
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar um novo calendário</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.calendars.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Informações do Calendário --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações do Calendário
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-tag me-1"></i>
                                            Nome <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" 
                                               placeholder="Ex: Calendário Principal" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-link me-1"></i>
                                            ID Externo
                                        </label>
                                        <input type="text" class="form-control @error('external_id') is-invalid @enderror" 
                                               name="external_id" value="{{ old('external_id') }}" 
                                               placeholder="ID do calendário em sistema externo (opcional)">
                                        <small class="form-text text-muted">ID usado para sincronização com calendários externos</small>
                                        @error('external_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.calendars.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Calendário
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
<style>
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .card-title {
        font-weight: 600;
    }
    h5.text-primary {
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
</style>
@endpush

@endsection

