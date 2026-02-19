@extends('layouts.tailadmin.app')

@section('title', 'Editar Paciente')
@section('page', 'patients')

@section('content')


    <div id="patients-address-config"
         data-states-url="{{ route('api.public.estados', ['pais' => 31]) }}"
         data-cities-url-template="{{ route('api.public.cidades', ['estado' => '__ID__']) }}"
         data-current-state-id="{{ $patient->address->estado_id ?? '' }}"
         data-current-city-id="{{ $patient->address->cidade_id ?? '' }}">
    </div>

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.patients.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Pacientes</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Editar</span>
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
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="pencil-outline" size="text-xl" class="mr-2 text-blue-600" />
                    Editar Paciente
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Atualize as Informações do paciente abaixo</p>
            </div>

                    <form action="{{ workspace_route('tenant.patients.update', $patient->id) }}" method="POST" class="p-6 space-y-8">
                        @csrf
                        @method('PUT')

                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <x-icon name="account-outline" size="text-lg" class="mr-2 text-blue-600" />
                                Dados Pessoais
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nome Completo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('full_name') border-red-500 @enderror"
                                           name="full_name" value="{{ old('full_name', $patient->full_name) }}"
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
                                           name="cpf" value="{{ old('cpf', $patient->cpf) }}"
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
                                           name="birth_date" value="{{ old('birth_date', $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '') }}">
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
                                            <option value="{{ $gender->id }}" {{ old('gender_id', $patient->gender_id) == $gender->id ? 'selected' : '' }}>
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
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <x-icon name="email-outline" size="text-lg" class="mr-2 text-blue-600" />
                                Informações de Contato
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        E-mail
                                    </label>
                                    <input type="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror"
                                           name="email" value="{{ old('email', $patient->email) }}"
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
                                           name="phone" value="{{ old('phone', $patient->phone) }}"
                                           maxlength="20" placeholder="(00) 00000-0000">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <x-icon name="map-marker-outline" size="text-lg" class="mr-2 text-blue-600" />
                                Endereço
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="md:col-span-4">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Logradouro <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('street') border-red-500 @enderror"
                                           id="address" name="street" value="{{ old('street', $patient->address->street ?? '') }}"
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
                                           name="number" value="{{ old('number', $patient->address->number ?? '') }}"
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
                                           name="complement" value="{{ old('complement', $patient->address->complement ?? '') }}"
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
                                           id="neighborhood" name="neighborhood" value="{{ old('neighborhood', $patient->address->neighborhood ?? '') }}"
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
                                           id="zipcode" name="postal_code" value="{{ old('postal_code', $patient->address->postal_code ?? '') }}"
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
                                    <input type="hidden" name="state" id="state_abbr" value="{{ old('state', $patient->address->state ?? '') }}">
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
                                    <input type="hidden" name="city" id="city_name" value="{{ old('city', $patient->address->city ?? '') }}">
                                    @error('cidade_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <x-icon name="check-circle-outline" size="text-lg" class="mr-2 text-blue-600" />
                                Status
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status do Paciente
                                    </label>
                                    <select name="is_active" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('is_active') border-red-500 @enderror">
                                        <option value="1" {{ old('is_active', $patient->is_active) == 1 ? 'selected' : '' }}>Ativo</option>
                                        <option value="0" {{ old('is_active', $patient->is_active) == 0 ? 'selected' : '' }}>Inativo</option>
                                    </select>
                                    @error('is_active')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ workspace_route('tenant.patients.index') }}" class="btn btn-outline inline-flex items-center">
                                <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-primary inline-flex items-center">
                                <x-icon name="content-save-outline" size="text-sm" class="mr-2" />
                                Atualizar Paciente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



@endsection


