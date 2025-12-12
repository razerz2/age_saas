@extends('layouts.connect_plus.app')

@section('title', 'Criar Paciente')

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0"> Criar Paciente </h3>
            <x-help-button module="patients" />
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.patients.index') }}">Pacientes</a>
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
                                <i class="mdi mdi-account-plus text-primary me-2"></i>
                                Novo Paciente
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para cadastrar um novo paciente</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ workspace_route('tenant.patients.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Dados Pessoais --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-account-outline me-2"></i>
                                Dados Pessoais
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Nome Completo <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                               name="full_name" value="{{ old('full_name') }}" 
                                               placeholder="Digite o nome completo do paciente" required>
                                        @error('full_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-card-account-details me-1"></i>
                                            CPF <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                               name="cpf" value="{{ old('cpf') }}" 
                                               maxlength="14" placeholder="000.000.000-00" required>
                                        @error('cpf')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar me-1"></i>
                                            Data de Nascimento
                                        </label>
                                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                               name="birth_date" value="{{ old('birth_date') }}">
                                        @error('birth_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-gender-male-female me-1"></i>
                                            Gênero
                                        </label>
                                        <select name="gender_id" class="form-control @error('gender_id') is-invalid @enderror">
                                            <option value="">Selecione...</option>
                                            @foreach($genders as $gender)
                                                <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>
                                                    {{ $gender->name }} ({{ $gender->abbreviation }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gender_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Contato --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-phone me-2"></i>
                                Informações de Contato
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-email me-1"></i>
                                            E-mail
                                        </label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" 
                                               placeholder="exemplo@email.com">
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-phone me-1"></i>
                                            Telefone
                                        </label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               name="phone" value="{{ old('phone') }}" 
                                               maxlength="20" placeholder="(00) 00000-0000">
                                        @error('phone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Endereço --}}
                        <div class="mb-4 patient-address-section">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-map-marker me-2"></i>
                                Endereço (Opcional)
                            </h5>
                            
                            {{-- Linha 1: Logradouro e Número --}}
                            <div class="row patient-address-row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-road me-1"></i>
                                            Logradouro
                                        </label>
                                        <input type="text" class="form-control @error('street') is-invalid @enderror" 
                                               name="street" value="{{ old('street') }}" 
                                               placeholder="Rua, Avenida, etc.">
                                        @error('street')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-numeric me-1"></i>
                                            Número
                                        </label>
                                        <input type="text" class="form-control @error('number') is-invalid @enderror" 
                                               name="number" value="{{ old('number') }}" 
                                               maxlength="20" placeholder="123">
                                        @error('number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Linha 2: Complemento e Bairro --}}
                            <div class="row patient-address-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-home-variant me-1"></i>
                                            Complemento
                                        </label>
                                        <input type="text" class="form-control @error('complement') is-invalid @enderror" 
                                               name="complement" value="{{ old('complement') }}" 
                                               placeholder="Apto, Bloco, etc.">
                                        @error('complement')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-city me-1"></i>
                                            Bairro
                                        </label>
                                        <input type="text" class="form-control @error('neighborhood') is-invalid @enderror" 
                                               name="neighborhood" value="{{ old('neighborhood') }}" 
                                               placeholder="Nome do bairro">
                                        @error('neighborhood')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Linha 3: Cidade, Estado e CEP --}}
                            <div class="row patient-address-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-city-variant me-1"></i>
                                            Cidade
                                        </label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                               name="city" value="{{ old('city') }}" 
                                               placeholder="Nome da cidade">
                                        @error('city')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-map-marker-radius me-1"></i>
                                            Estado (UF)
                                        </label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                               name="state" value="{{ old('state') }}" 
                                               maxlength="2" placeholder="SP" style="text-transform: uppercase;">
                                        @error('state')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-postal-code me-1"></i>
                                            CEP
                                        </label>
                                        <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                               name="postal_code" value="{{ old('postal_code') }}" 
                                               maxlength="10" placeholder="00000-000">
                                        @error('postal_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Status --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-toggle-switch me-2"></i>
                                Status
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-check-circle me-1"></i>
                                            Status do Paciente
                                        </label>
                                        <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ workspace_route('tenant.patients.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Paciente
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <style>
        /* CSS inline para garantir que os campos fiquem lado a lado com espaçamento adequado */
        .patient-address-section .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
            --bs-gutter-x: 0 !important;
            --bs-gutter-y: 0 !important;
        }
        /* Espaçamento entre campos de endereço */
        .patient-address-section .row > [class*="col-"] {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .patient-address-section .row > [class*="col-"]:first-child {
            padding-left: 0 !important;
            padding-right: 0.75rem !important;
        }
        .patient-address-section .row > [class*="col-"]:last-child {
            padding-right: 0 !important;
            padding-left: 0.75rem !important;
        }
        .patient-address-section .row > [class*="col-"]:not(:first-child):not(:last-child) {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
    </style>
@endpush

@endsection
