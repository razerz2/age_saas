@extends('layouts.tailadmin.app')

@section('title', 'Gerenciar Login do Paciente')
@section('page', 'patients')

@section('content')

    <div id="patients-login-form-config"
         data-require-confirmation="{{ (!isset($patient->login) || !$patient->login) ? '1' : '0' }}"></div>

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="mdi-chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.patients.index') }}" class="hover:text-blue-600 dark:hover:text-white">Pacientes</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="mdi-chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Gerenciar Login</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-icon name="mdi-check-circle-outline" size="text-lg" class="text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-icon name="mdi-alert-circle-outline" size="text-lg" class="text-red-400" />
                </div>
                <div class="ml-3">
                    <div class="text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Card Dados do Paciente -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="mdi-account-outline" size="text-lg" class="mr-2 text-blue-600" />
                        Dados do Paciente
                    </h4>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $patient->full_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">CPF</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $patient->cpf ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">E-mail</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $patient->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Login -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="mdi-key-outline" size="text-lg" class="mr-2 text-blue-600" />
                        {{ (isset($patient->login) && $patient->login) ? 'Editar Login' : 'Criar Login' }}
                    </h4>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ workspace_route('tenant.patients.login.store', $patient->id) }}" class="space-y-6">
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                E-mail para Login <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', (isset($patient->login) && $patient->login) ? $patient->login->email : '') }}" 
                                   placeholder="Digite o e-mail para login"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este será o e-mail usado para acesso ao portal do paciente.</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Senha {{ (isset($patient->login) && $patient->login) ? '(deixe em branco para não alterar)' : '*' }}
                            </label>
                            <div class="flex gap-2">
                                <input type="password" 
                                       class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('password') border-red-500 @enderror" 
                                       id="password" 
                                       name="password"
                                       {{ !(isset($patient->login) && $patient->login) ? 'required' : '' }}
                                       minlength="6">
                                <button type="button" 
                                        id="generatePassword" 
                                        class="btn btn-outline inline-flex items-center"
                                        title="Gerar senha aleatória">
                                    <x-icon name="mdi-refresh" size="text-sm" class="mr-2" />
                                    Gerar
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mínimo de 6 caracteres.</p>
                        </div>

                        @if(!isset($patient->login) || !$patient->login)
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Confirmar Senha <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('password_confirmation') border-red-500 @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       required
                                       minlength="6">
                                @error('password_confirmation')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                            </div>
                        @endif

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', (isset($patient->login) && $patient->login) ? $patient->login->is_active : true) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Acesso Ativo</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Desmarque para bloquear o acesso ao portal.</p>
                        </div>

                        <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ workspace_route('tenant.patients.index') }}" class="btn btn-outline">
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
