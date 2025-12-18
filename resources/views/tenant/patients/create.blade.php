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
                                Endereço
                            </h5>
                            
                            {{-- Linha 1: Logradouro --}}
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-road me-1"></i>
                                            Logradouro <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('street') is-invalid @enderror" 
                                               id="address" name="street" value="{{ old('street') }}" 
                                               placeholder="Rua, Avenida, etc." required>
                                        @error('street')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Linha 2: Número, Complemento e Bairro --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-numeric me-1"></i>
                                            Número <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('number') is-invalid @enderror" 
                                               name="number" value="{{ old('number') }}" 
                                               maxlength="20" placeholder="123" required>
                                        @error('number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-city me-1"></i>
                                            Bairro <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('neighborhood') is-invalid @enderror" 
                                               id="neighborhood" name="neighborhood" value="{{ old('neighborhood') }}" 
                                               placeholder="Nome do bairro" required>
                                        @error('neighborhood')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Linha 3: CEP, Estado e Cidade --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-postal-code me-1"></i>
                                            CEP <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                               id="zipcode" name="postal_code" value="{{ old('postal_code') }}" 
                                               maxlength="10" placeholder="00000-000" required>
                                        @error('postal_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <input type="hidden" name="pais_id" value="31"> {{-- Brasil fixo --}}

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-map-marker-radius me-1"></i>
                                            Estado <span class="text-danger">*</span>
                                        </label>
                                        <select id="state_id" name="estado_id" class="form-control @error('estado_id') is-invalid @enderror" required>
                                            <option value="">Carregando...</option>
                                        </select>
                                        <input type="hidden" name="state" id="state_abbr">
                                        @error('estado_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-city-variant me-1"></i>
                                            Cidade <span class="text-danger">*</span>
                                        </label>
                                        <select id="city_id" name="cidade_id" class="form-control @error('cidade_id') is-invalid @enderror" required>
                                            <option value="">Selecione o estado</option>
                                        </select>
                                        <input type="hidden" name="city" id="city_name">
                                        @error('cidade_id')
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stateSelect = document.getElementById('state_id');
            const citySelect = document.getElementById('city_id');
            const zipcodeField = document.getElementById('zipcode');
            const addressField = document.getElementById('address');
            const neighborhoodField = document.getElementById('neighborhood');
            const stateAbbrInput = document.getElementById('state_abbr');
            const cityNameInput = document.getElementById('city_name');

            async function loadStates() {
                stateSelect.innerHTML = '<option value="">Carregando estados...</option>';
                try {
                    const response = await fetch('{{ route('api.public.estados', ['pais' => 31]) }}');
                    const data = await response.json();
                    stateSelect.innerHTML = '<option value="">Selecione o estado</option>';
                    data.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state.id_estado;
                        option.dataset.abbr = state.uf;
                        option.textContent = state.nome_estado;
                        stateSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar estados:', error);
                    stateSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            }

            async function loadCities(stateId) {
                if (!stateId) {
                    citySelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
                    return;
                }
                citySelect.innerHTML = '<option value="">Carregando cidades...</option>';
                try {
                    const response = await fetch('{{ route('api.public.cidades', ['estado' => ':id']) }}'.replace(':id', stateId));
                    const data = await response.json();
                    citySelect.innerHTML = '<option value="">Selecione a cidade</option>';
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id_cidade;
                        option.dataset.name = city.nome_cidade;
                        option.textContent = city.nome_cidade;
                        citySelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar cidades:', error);
                    citySelect.innerHTML = '<option value="">Erro ao carregar</option>';
                }
            }

            if (stateSelect) {
                stateSelect.addEventListener('change', function() {
                    loadCities(this.value);
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.abbr) {
                        stateAbbrInput.value = selectedOption.dataset.abbr;
                    }
                });
            }

            if (citySelect) {
                citySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.name) {
                        cityNameInput.value = selectedOption.dataset.name;
                    }
                });
            }

            if (zipcodeField) {
                zipcodeField.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 8) value = value.substring(0, 8);
                    if (value.length > 5) {
                        value = value.substring(0, 5) + '-' + value.substring(5);
                    }
                    e.target.value = value;

                    if (value.replace(/\D/g, '').length === 8) {
                        fetch(`https://viacep.com.br/ws/${value.replace(/\D/g, '')}/json/`)
                            .then(response => response.json())
                            .then(data => {
                                if (!data.erro) {
                                    if (addressField) addressField.value = data.logradouro;
                                    if (neighborhoodField) neighborhoodField.value = data.bairro;
                                    
                                    // Tenta selecionar o estado pelo UF
                                    if (data.uf) {
                                        for (let i = 0; i < stateSelect.options.length; i++) {
                                            if (stateSelect.options[i].dataset.abbr === data.uf) {
                                                stateSelect.selectedIndex = i;
                                                stateAbbrInput.value = data.uf;
                                                loadCities(stateSelect.value).then(() => {
                                                    // Tenta selecionar a cidade pelo nome
                                                    if (data.localidade) {
                                                        for (let j = 0; j < citySelect.options.length; j++) {
                                                            if (citySelect.options[j].dataset.name.toLowerCase() === data.localidade.toLowerCase()) {
                                                                citySelect.selectedIndex = j;
                                                                cityNameInput.value = data.localidade;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                });
                                                break;
                                            }
                                        }
                                    }
                                }
                            });
                    }
                });
            }

            loadStates();
        });
    </script>
@endpush

@endsection
