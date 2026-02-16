@extends('layouts.tailadmin.app')

@section('title', 'Criar Paciente')

@section('content')

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ workspace_route('tenant.patients.index') }}" class="hover:text-blue-600 dark:hover:text-white">Pacientes</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="patients" />
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">Novo Paciente</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preencha os dados abaixo para cadastrar um novo paciente</p>
            </div>

            <form action="{{ workspace_route('tenant.patients.store') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dados Pessoais</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('full_name') border-red-500 @enderror"
                                   name="full_name" value="{{ old('full_name') }}"
                                   placeholder="Digite o nome completo do paciente" required>
                            @error('full_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                CPF <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('cpf') border-red-500 @enderror"
                                   name="cpf" value="{{ old('cpf') }}"
                                   maxlength="14" placeholder="000.000.000-00" required>
                            @error('cpf')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Data de Nascimento
                            </label>
                            <input type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('birth_date') border-red-500 @enderror"
                                   name="birth_date" value="{{ old('birth_date') }}">
                            @error('birth_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Gênero
                            </label>
                            <select name="gender_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('gender_id') border-red-500 @enderror">
                                <option value="">Selecione...</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>
                                        {{ $gender->name }} ({{ $gender->abbreviation }})
                                    </option>
                                @endforeach
                            </select>
                            @error('gender_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações de Contato</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                E-mail
                            </label>
                            <input type="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror"
                                   name="email" value="{{ old('email') }}"
                                   placeholder="exemplo@email.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Telefone
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('phone') border-red-500 @enderror"
                                   name="phone" value="{{ old('phone') }}"
                                   maxlength="20" placeholder="(00) 00000-0000">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Endereço</h5>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Logradouro <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('street') border-red-500 @enderror"
                                   id="address" name="street" value="{{ old('street') }}"
                                   placeholder="Rua, Avenida, etc." required>
                            @error('street')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Número <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('number') border-red-500 @enderror"
                                   name="number" value="{{ old('number') }}"
                                   maxlength="20" placeholder="123" required>
                            @error('number')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Complemento
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('complement') border-red-500 @enderror"
                                   name="complement" value="{{ old('complement') }}"
                                   placeholder="Apto, Bloco, etc.">
                            @error('complement')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Bairro <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('neighborhood') border-red-500 @enderror"
                                   id="neighborhood" name="neighborhood" value="{{ old('neighborhood') }}"
                                   placeholder="Nome do bairro" required>
                            @error('neighborhood')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                CEP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('postal_code') border-red-500 @enderror"
                                   id="zipcode" name="postal_code" value="{{ old('postal_code') }}"
                                   maxlength="10" placeholder="00000-000" required>
                            @error('postal_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="pais_id" value="31">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select id="state_id" name="estado_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('estado_id') border-red-500 @enderror" required>
                                <option value="">Carregando...</option>
                            </select>
                            <input type="hidden" name="state" id="state_abbr">
                            @error('estado_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Cidade <span class="text-red-500">*</span>
                            </label>
                            <select id="city_id" name="cidade_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('cidade_id') border-red-500 @enderror" required>
                                <option value="">Selecione o estado</option>
                            </select>
                            <input type="hidden" name="city" id="city_name">
                            @error('cidade_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status do Paciente
                            </label>
                            <select name="is_active" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('is_active') border-red-500 @enderror">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.patients.index') }}" class="btn-patient-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        Salvar Paciente
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <style>
        .btn-patient-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: #2563eb;
            color: white;
        }
        
        .btn-patient-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .btn-patient-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: transparent;
            color: #374151;
            text-decoration: none;
        }
        
        .btn-patient-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }
        
        /* Dark mode styles */
        @media (prefers-color-scheme: dark) {
            .btn-patient-primary {
                background-color: transparent;
                border-color: #d1d5db;
                color: white;
            }
            
            .btn-patient-primary:hover {
                background-color: #1f2937;
                border-color: #9ca3af;
            }
            
            .btn-patient-secondary {
                background-color: transparent;
                border-color: #d1d5db;
                color: white;
            }
            
            .btn-patient-secondary:hover {
                background-color: #1f2937;
                border-color: #9ca3af;
            }
        }
        
        /* For TailAdmin dark mode class */
        .dark .btn-patient-primary {
            background-color: transparent;
            border-color: #d1d5db;
            color: white;
        }
        
        .dark .btn-patient-primary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
        }
        
        .dark .btn-patient-secondary {
            background-color: transparent;
            border-color: #d1d5db;
            color: white;
        }
        
        .dark .btn-patient-secondary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
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
