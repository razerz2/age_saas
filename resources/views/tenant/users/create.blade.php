@extends('layouts.tailadmin.app')

@section('title', 'Criar Usuário')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ workspace_route('tenant.users.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Usuários</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="users" />
            </div>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Novo Usuário</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Preencha os dados abaixo para cadastrar um novo usuário</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ workspace_route('tenant.users.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Seção: Dados Pessoais -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Dados Pessoais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name_full" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name_full" id="name_full"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('name_full') border-red-300 @enderror"
                                value="{{ old('name_full') }}" placeholder="Digite o nome completo" required>
                            @error('name_full')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nome de Exibição <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('name') border-red-300 @enderror"
                                value="{{ old('name') }}" placeholder="Digite o nome de exibição" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Foto de Perfil
                        </label>
                        <div class="flex gap-3 mb-2">
                            <input type="file" name="avatar" id="avatar-input" 
                                class="flex-1 px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('avatar') border-red-300 @enderror"
                                accept="image/*">
                            <button type="button" id="webcam-btn" class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Webcam
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Pré-visualização da imagem -->
                        <div id="avatar-preview-container" class="mt-3 hidden">
                            <div class="flex items-center gap-4">
                                <div>
                                    <img id="avatar-preview" src="" alt="Preview" 
                                         class="w-24 h-24 rounded-full border-4 border-gray-200 object-cover">
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-green-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span id="avatar-filename"></span>
                                    </p>
                                    <button type="button" id="avatar-remove" class="mt-2 px-3 py-1.5 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Remover imagem
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção: Contato e Acesso -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.387-.795a1 1 0 01-.617-.617L6.882 7.815A1 1 0 016.28 7H5a2 2 0 00-2 2v6a2 2 0 002 2h1.28a1 1 0 01.948-.684l1.498-4.493a1 1 0 01.502-1.21L9.882 16.185a1 1 0 01.617.617L12.118 16.4A1 1 0 0112.72 17H15a2 2 0 002-2V5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0l7.89-5.26"></path>
                        </svg>
                        Contato e Acesso
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Telefone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="telefone" id="telefone"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('telefone') border-red-300 @enderror"
                                value="{{ old('telefone') }}" placeholder="(00) 00000-0000" required>
                            @error('telefone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                E-mail
                            </label>
                            <input type="email" name="email" id="email"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('email') border-red-300 @enderror"
                                value="{{ old('email') }}" placeholder="exemplo@email.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Senha
                            </label>
                            <div class="flex w-full">
                                <input type="password" name="password" id="password"
                                    class="w-full min-w-0 flex-1 px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('password') border-red-300 @enderror"
                                    placeholder="Digite a senha">
                                <button type="button" onclick="togglePasswordVisibility('password')" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    title="Mostrar/Ocultar senha">
                                    <svg class="w-4 h-4" id="password-eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button type="button" onclick="generatePassword()" 
                                    class="shrink-0 w-24 inline-flex items-center justify-center gap-1.5 px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-r-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Gerar
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mínimo 8 caracteres com maiúscula, minúscula, número e caractere especial</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Confirmar Senha
                            </label>
                            <div class="flex w-full">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full min-w-0 flex-1 px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('password_confirmation') border-red-300 @enderror"
                                    placeholder="Confirme a senha">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation')" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    title="Mostrar/Ocultar senha">
                                    <svg class="w-4 h-4" id="password_confirmation-eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <div class="shrink-0 w-24 inline-flex items-center justify-center px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-400 dark:text-gray-300 rounded-r-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Configurações -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.37a1.724 1.724 0 00-2.572-1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31.826-2.37.826a1.724 1.724 0 00-1.065-2.572c1.756-.426 1.756-2.924 0-3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.37a1.724 1.724 0 00-2.572-1.065c-.426 1.756-2.924 1.756-3.35 0z"></path>
                        </svg>
                        Configurações
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Perfil <span class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role-select" 
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('role') border-red-300 @enderror" required>
                                <option value="user" {{ old('role', 'user') == 'user' ? 'selected' : '' }}>Usuário Comum</option>
                                <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Usuário Médico</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="is-doctor-section" class="hidden">
                            <label for="is-doctor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                É Médico?
                            </label>
                            <select name="is_doctor" id="is-doctor-select"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white @error('is_doctor') border-red-300 @enderror">
                                <option value="0" {{ old('is_doctor', '0') == '0' ? 'selected' : '' }}>Não</option>
                                <option value="1" {{ old('is_doctor') == '1' ? 'selected' : '' }}>Sim</option>
                            </select>
                            @error('is_doctor')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Permissões de Médicos (para usuário comum) -->
                <div class="mb-8 hidden" id="doctor-permissions-section">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Médicos Permitidos
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione os médicos que este usuário pode visualizar:</label>
                        @php
                            $doctors = \App\Models\Tenant\Doctor::with('user')->get();
                            $oldDoctorIds = old('doctor_ids', []);
                        @endphp
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                            <div class="flex flex-wrap gap-4">
                                @foreach($doctors as $doctor)
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                            name="doctor_ids[]"
                                            value="{{ $doctor->id }}" 
                                            {{ in_array($doctor->id, $oldDoctorIds) ? 'checked' : '' }}>
                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $doctor->user->name_full ?? $doctor->user->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('doctor_ids')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Seção: Módulos -->
                <div class="mb-8" id="modules-section">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Módulos
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione os módulos disponíveis para este usuário:</label>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm text-blue-800 dark:text-blue-200">
                                    <p id="modules-info-text">
                                        <strong>Nota:</strong> Os módulos serão pré-selecionados conforme as configurações padrão em <a href="{{ workspace_route('tenant.settings.index') }}" target="_blank" class="text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @php
                            // Buscar apenas módulos disponíveis (no plano e habilitados na tenant)
                            $availableModules = App\Models\Tenant\Module::available();
                            // Sempre remover módulo "usuários" - apenas admins têm acesso, mas não podem atribuir a outros
                                    $modules = collect($availableModules)->reject(function($module) {
                                return $module['key'] === 'users';
                            })->values()->all();
                            
                            // Carregar módulos padrão baseado no role inicial
                            $initialRole = old('role', 'user');
                            $defaultModules = [];
                            if ($initialRole === 'doctor') {
                                $defaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
                            } elseif ($initialRole === 'user') {
                                $defaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
                            }
                            
                            // Se houver old('modules'), usar ele, senão usar os padrões
                            $oldModules = old('modules', $defaultModules);
                        @endphp
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                            <div class="flex flex-wrap gap-4">
                                @foreach($modules as $module)
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded module-checkbox" 
                                            name="modules[]"
                                            value="{{ $module['key'] }}" 
                                            data-module-key="{{ $module['key'] }}"
                                            {{ in_array($module['key'], $oldModules) ? 'checked' : '' }}>
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $module['name'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('modules')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.users.index') }}" class="btn-patient-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5m-4-4v5h18a2 2 0 002-2v-5a2 2 0 00-2-2h-5z"></path>
                        </svg>
                        Salvar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal da Webcam -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="webcam-modal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Capturar Foto
                </h3>
                <button onclick="closeWebcamModal()" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293 4.293a1 1 0 001.414 1.414L10 10.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10l-4.293-4.293a1 1 0 00-1.414 0L2.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-4">
                <video id="webcam-video" autoplay playsinline class="w-full rounded-lg hidden"></video>
                <canvas id="webcam-canvas" class="hidden"></canvas>
                <div id="webcam-placeholder" class="p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Clique em "Iniciar Webcam" para começar</p>
                </div>
            </div>
            <div class="mt-6 flex justify-center space-x-6">
                <button type="button" id="webcam-start" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-blue-500" style="margin-right: 12px !important;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0021 8.618v6.764a1 1 0 01-1.447.894L15 14V10z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 18h16"></path>
                    </svg>
                    Iniciar Webcam
                </button>
                <button type="button" id="webcam-capture" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 hidden" style="margin-right: 12px !important;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Capturar Foto
                </button>
                <button type="button" id="webcam-stop" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 hidden" style="margin-right: 12px !important;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                    </svg>
                    Parar
                </button>
                <button type="button" onclick="closeWebcamModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-users.css') }}" rel="stylesheet">
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
        
        /* Ensure webcam buttons are visible */
        #webcam-start, #webcam-capture, #webcam-stop {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 10 !important;
        }
        
        #webcam-start.hidden, #webcam-capture.hidden, #webcam-stop.hidden {
            display: none !important;
        }
        
        /* Force button visibility in light mode */
        #webcam-start {
            background-color: #2563eb !important;
            color: white !important;
            border: none !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem !important;
            font-weight: 500 !important;
        }
        
        #webcam-start:hover {
            background-color: #1d4ed8 !important;
        }
    </style>
@endpush

@push('scripts')
<script src="{{ asset('js/password-generator.js') }}"></script>
<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-eye-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
        } else {
            field.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        }
    }
    
    function generatePassword() {
        const password = generateStrongPassword();
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Mostra temporariamente
        document.getElementById('password').type = 'text';
        document.getElementById('password_confirmation').type = 'text';
        document.getElementById('password-eye-icon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
        document.getElementById('password_confirmation-eye-icon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
        document.getElementById('password').select();
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            document.getElementById('password').type = 'password';
            document.getElementById('password_confirmation').type = 'password';
            document.getElementById('password-eye-icon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            document.getElementById('password_confirmation-eye-icon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        }, 3000);
    }
    
    function closeWebcamModal() {
        document.getElementById('webcam-modal').classList.add('hidden');
        stopWebcam();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreviewContainer = document.getElementById('avatar-preview-container');
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarFilename = document.getElementById('avatar-filename');
        const avatarRemove = document.getElementById('avatar-remove');
        const roleSelect = document.getElementById('role-select');
        const isDoctorSelect = document.getElementById('is-doctor-select');
        const doctorPermissionsSection = document.getElementById('doctor-permissions-section');
        const modulesSection = document.getElementById('modules-section');
        const loggedUserRole = '{{ Auth::guard("tenant")->user()->role ?? "" }}';

        // Função para exibir pré-visualização
        function showPreview(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                    avatarFilename.textContent = file.name;
                    avatarPreviewContainer.classList.remove('hidden');
                };
                
                reader.readAsDataURL(file);
            } else {
                showAlert({ type: 'warning', title: 'Atenção', message: 'Por favor, selecione um arquivo de imagem válido.' });
                avatarInput.value = '';
            }
        }

        // Event listener para mudança no input
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamanho (2MB)
                if (file.size > 2048 * 1024) {
                    showAlert({ type: 'warning', title: 'Atenção', message: 'O arquivo é muito grande. Por favor, selecione uma imagem com no máximo 2MB.' });
                    avatarInput.value = '';
                    avatarPreviewContainer.classList.add('hidden');
                    return;
                }
                showPreview(file);
            }
        });

        // Botão para remover imagem
        avatarRemove.addEventListener('click', function() {
            avatarInput.value = '';
            avatarPreviewContainer.classList.add('hidden');
            avatarPreview.src = '';
            avatarFilename.textContent = '';
        });

        // Webcam functionality
        const webcamBtn = document.getElementById('webcam-btn');
        const webcamVideo = document.getElementById('webcam-video');
        const webcamCanvas = document.getElementById('webcam-canvas');
        const webcamPlaceholder = document.getElementById('webcam-placeholder');
        const webcamStart = document.getElementById('webcam-start');
        const webcamCapture = document.getElementById('webcam-capture');
        const webcamStop = document.getElementById('webcam-stop');
        let stream = null;

        // Debug: Verificar se todos os elementos foram encontrados
        console.log('Webcam elements found:', {
            webcamBtn: !!webcamBtn,
            webcamVideo: !!webcamVideo,
            webcamCanvas: !!webcamCanvas,
            webcamPlaceholder: !!webcamPlaceholder,
            webcamStart: !!webcamStart,
            webcamCapture: !!webcamCapture,
            webcamStop: !!webcamStop
        });

        // Abrir modal da webcam
        if (webcamBtn) {
            webcamBtn.addEventListener('click', function() {
                console.log('Opening webcam modal');
                const modal = document.getElementById('webcam-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    
                    // Forçar visibilidade do botão Iniciar Webcam
                    setTimeout(() => {
                        if (webcamStart) {
                            webcamStart.classList.remove('hidden');
                            webcamStart.style.display = 'inline-flex !important';
                            webcamStart.style.visibility = 'visible !important';
                            webcamStart.style.opacity = '1 !important';
                            
                            // Verificar se o botão está realmente visível
                            const rect = webcamStart.getBoundingClientRect();
                            console.log('webcam-start button dimensions:', {
                                width: rect.width,
                                height: rect.height,
                                visible: rect.width > 0 && rect.height > 0,
                                display: window.getComputedStyle(webcamStart).display,
                                visibility: window.getComputedStyle(webcamStart).visibility,
                                opacity: window.getComputedStyle(webcamStart).opacity
                            });
                            console.log('webcam-start button should be visible now');
                        } else {
                            console.error('webcam-start button element not found');
                        }
                    }, 100);
                } else {
                    console.error('Webcam modal not found');
                }
            });
        } else {
            console.error('webcam-btn not found');
        }

        // Iniciar webcam
        if (webcamStart) {
            webcamStart.addEventListener('click', async function() {
                console.log('Starting webcam...');
                console.log('User agent:', navigator.userAgent);
                console.log('Protocol:', window.location.protocol);
                console.log('navigator.mediaDevices:', !!navigator.mediaDevices);
                
                try {
                    // Verificar se está em HTTPS (requerido para webcam)
                    if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                        throw new Error('Acesso à webcam requer conexão HTTPS. Por favor, acesse esta página através de HTTPS.');
                    }
                    
                    // Verificar se o navegador suporta getUserMedia
                    if (!navigator.mediaDevices) {
                        // Tentar detectar navegadores antigos
                        const isOldBrowser = /MSIE|Trident|Edge\/12|Edge\/13/.test(navigator.userAgent);
                        if (isOldBrowser) {
                            throw new Error('Seu navegador é muito antigo e não suporta acesso à webcam. Por favor, atualize para uma versão recente do Chrome, Firefox ou Edge.');
                        } else {
                            throw new Error('Seu navegador não suporta acesso à webcam. Verifique se você está usando um navegador moderno e se a página está acessada via HTTPS.');
                        }
                    }
                    
                    // Verificar se getUserMedia está disponível
                    if (typeof navigator.mediaDevices.getUserMedia !== 'function') {
                        throw new Error('A função getUserMedia não está disponível. Isso pode ser um problema de compatibilidade do navegador.');
                    }
                    
                    console.log('Attempting to getUserMedia...');
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            width: { ideal: 640 },
                            height: { ideal: 480 },
                            facingMode: 'user'
                        } 
                    });
                    
                    console.log('Webcam access granted');
                    webcamVideo.srcObject = stream;
                    webcamVideo.classList.remove('hidden');
                    webcamPlaceholder.classList.add('hidden');
                    webcamStart.classList.add('hidden');
                    webcamCapture.classList.remove('hidden');
                    webcamStop.classList.remove('hidden');
                } catch (err) {
                    console.error('Webcam error details:', {
                        name: err.name,
                        message: err.message,
                        stack: err.stack
                    });
                    
                    let errorMessage = 'Erro ao acessar a webcam: ';
                    
                    if (err.name === 'NotAllowedError') {
                        errorMessage += 'Permissão negada. Por favor, clique no ícone de câmera na barra de endereço e permita o acesso à webcam.';
                    } else if (err.name === 'NotFoundError') {
                        errorMessage += 'Nenhuma webcam encontrada. Verifique se sua webcam está conectada e funcionando.';
                    } else if (err.name === 'NotReadableError') {
                        errorMessage += 'A webcam já está sendo usada por outro aplicativo. Feche outros aplicativos que possam estar usando a câmera.';
                    } else if (err.name === 'OverconstrainedError') {
                        errorMessage += 'A webcam não suporta as configurações solicitadas. Tente novamente.';
                    } else if (err.name === 'SecurityError') {
                        errorMessage += 'Erro de segurança. Verifique se a página está acessada via HTTPS.';
                    } else {
                        errorMessage += err.message;
                    }
                    
                    showAlert({ type: 'error', title: 'Erro', message: errorMessage });
                }
            });
        } else {
            console.error('webcam-start button not found');
        }

        // Capturar foto
        if (webcamCapture) {
            webcamCapture.addEventListener('click', function() {
                console.log('Capturing photo...');
                const context = webcamCanvas.getContext('2d');
                webcamCanvas.width = webcamVideo.videoWidth;
                webcamCanvas.height = webcamVideo.videoHeight;
                context.drawImage(webcamVideo, 0, 0);
                
                // Converter canvas para blob
                webcamCanvas.toBlob(function(blob) {
                    if (blob) {
                        // Criar arquivo a partir do blob
                        const file = new File([blob], 'webcam-photo.jpg', { type: 'image/jpeg' });
                        
                        // Criar DataTransfer para adicionar ao input
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        avatarInput.files = dataTransfer.files;
                        
                        // Mostrar preview
                        showPreview(file);
                        
                        // Parar webcam e fechar modal
                        stopWebcam();
                        document.getElementById('webcam-modal').classList.add('hidden');
                    }
                }, 'image/jpeg', 0.9);
            });
        } else {
            console.error('webcam-capture button not found');
        }

        // Parar webcam
        if (webcamStop) {
            webcamStop.addEventListener('click', function() {
                console.log('Stopping webcam...');
                stopWebcam();
            });
        } else {
            console.error('webcam-stop button not found');
        }

        function stopWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            webcamVideo.srcObject = null;
            webcamVideo.classList.add('hidden');
            webcamPlaceholder.classList.remove('hidden');
            webcamStart.classList.remove('hidden');
            webcamCapture.classList.add('hidden');
            webcamStop.classList.add('hidden');
        }

        // Controlar exibição de seções baseado no role
        function toggleRoleSections() {
            const role = roleSelect.value;
            const isDoctorSection = document.getElementById('is-doctor-section');
            
            // Campo "É Médico?" - só aparece quando role é "admin"
            if (isDoctorSection) {
                if (role === 'admin') {
                    isDoctorSection.classList.remove('hidden');
                } else {
                    isDoctorSection.classList.add('hidden');
                    // Resetar valor quando ocultar
                    if (isDoctorSelect) {
                        isDoctorSelect.value = '0';
                    }
                }
            }
            
            // Ajustar campo "é médico" automaticamente quando role é doctor
            if (isDoctorSelect && role === 'doctor') {
                isDoctorSelect.value = '1'; // Marca como "Sim"
            }
            
            // Controlar exibição de "Médicos Permitidos"
            // Aparece se: role selecionado é "user" E usuário logado não é médico
            // NÃO aparece se role é "admin"
            if (doctorPermissionsSection) {
                if (role === 'user' && loggedUserRole !== 'doctor') {
                    doctorPermissionsSection.classList.remove('hidden');
                } else {
                    doctorPermissionsSection.classList.add('hidden');
                }
            }
            
            // Controlar exibição e pré-seleção de "Módulos"
            // NÃO aparece se role é "admin"
            if (modulesSection) {
                if (role === 'admin') {
                    modulesSection.classList.add('hidden');
                } else {
                    modulesSection.classList.remove('hidden');
                    
                    // Atualizar mensagem informativa
                    const modulesInfoText = document.getElementById('modules-info-text');
                    if (modulesInfoText) {
                        if (role === 'doctor') {
                            modulesInfoText.innerHTML = '<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para médicos em <a href="{{ workspace_route("tenant.settings.index") }}" target="_blank" class="text-blue-600 underline hover:text-blue-800">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.';
                        } else {
                            modulesInfoText.innerHTML = '<strong>Nota:</strong> Os módulos foram pré-selecionados conforme as configurações padrão para usuários comuns em <a href="{{ workspace_route("tenant.settings.index") }}" target="_blank" class="text-blue-600 underline hover:text-blue-800">Configurações → Usuários & Permissões</a>. Você pode ajustar manualmente se necessário.';
                        }
                    }
                    
                    // Pré-selecionar módulos padrão baseado no role
                    updateModulesSelection(role);
                }
            }
        }

        // Dados dos módulos padrão carregados do servidor
        @php
            $commonUserDefaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
            $doctorDefaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
        @endphp
        
        const defaultModulesData = {
            'user': @json($commonUserDefaultModules),
            'doctor': @json($doctorDefaultModules),
            'admin': []
        };

        // Função para atualizar seleção de módulos baseado no role
        function updateModulesSelection(role) {
            const defaultModules = defaultModulesData[role] || [];
            const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
            
            moduleCheckboxes.forEach(checkbox => {
                const moduleKey = checkbox.getAttribute('data-module-key');
                checkbox.checked = defaultModules.includes(moduleKey);
            });
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleRoleSections);
            // Executar na carga inicial
            toggleRoleSections();
        } else {
            console.error('roleSelect não encontrado');
        }
    });
</script>
@endpush

@endsection
