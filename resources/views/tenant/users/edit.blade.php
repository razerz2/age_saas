@extends('layouts.tailadmin.app')

@section('title', 'Editar Usuário')
@section('page', 'users')

@section('content')
@php
    $commonUserDefaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
    $doctorDefaultModules = json_decode(App\Models\Tenant\TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
@endphp
<div id="users-config" class="hidden"
    data-default-modules-user='@json($commonUserDefaultModules)'
    data-default-modules-doctor='@json($doctorDefaultModules)'
    data-logged-role='{{ Auth::guard("tenant")->user()->role ?? "" }}'
    data-settings-url="{{ workspace_route('tenant.settings.index') }}"
    data-initial-role="{{ old('role', $user->role ?? 'user') }}"
    data-original-avatar="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('tailadmin/assets/images/user/user-01.jpg') }}"
    data-has-original-avatar="{{ $user->avatar ? '1' : '0' }}">
</div>

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Usuário</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.users.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Usuários</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <x-help-button module="users" />
        </div>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        @foreach ($errors->all() as $error)
                            {{ $error }}{{ !$loop->last ? ' | ' : '' }}
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-900">Editar Usuário</h2>
                    <p class="text-sm text-gray-500">Atualize as informações do usuário abaixo</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ workspace_route('tenant.users.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Seção: Dados Pessoais -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Dados Pessoais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name_full" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name_full" id="name_full"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name_full') border-red-300 @enderror"
                                value="{{ old('name_full', $user->name_full) }}" placeholder="Digite o nome completo" required>
                            @error('name_full')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome de Exibição <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name') border-red-300 @enderror"
                                value="{{ old('name', $user->name) }}" placeholder="Digite o nome de exibição" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto de Perfil
                        </label>
                        <div class="flex gap-3 mb-2">
                            <input type="file" name="avatar" id="avatar-input" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('avatar') border-red-300 @enderror"
                                accept="image/*">
                            <button type="button" id="webcam-btn" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Webcam
                            </button>
                        </div>
                        <p class="text-sm text-gray-500">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Foto atual -->
                        @if ($user->avatar)
                            <div class="mt-3 flex items-center gap-4">
                                <div>
                                    <img src="{{ Storage::url($user->avatar) }}" alt="Foto atual" 
                                         class="w-20 h-20 rounded-full border-4 border-gray-200 object-cover">
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700 font-medium">Foto atual</p>
                                    <p class="text-sm text-gray-500">Deixe em branco para manter a foto atual</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Seção: Contato e Acesso -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Contato e Acesso
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                                Telefone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="telefone" id="telefone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('telefone') border-red-300 @enderror"
                                value="{{ old('telefone', $user->telefone) }}" placeholder="(00) 00000-0000" required>
                            @error('telefone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                E-mail
                            </label>
                            <input type="email" name="email" id="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('email') border-red-300 @enderror"
                                value="{{ old('email', $user->email) }}" placeholder="exemplo@email.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seção: Configurações -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.37a1.724 1.724 0 00-2.572-1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31.826-2.37.826a1.724 1.724 0 00-1.065-2.572C4.93 8.268 4.93 7.09 4.93 5.74a1.724 1.724 0 001.066-2.573c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31-.826 2.37-.826a1.724 1.724 0 001.065 2.572c1.756-.426 1.756-2.924 0-3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.37a1.724 1.724 0 00-2.572-1.065c-.426 1.756-2.924 1.756-3.35 0z"></path>
                        </svg>
                        Configurações
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                Perfil <span class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role-select" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('role') border-red-300 @enderror" required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Usuário Comum</option>
                                <option value="doctor" {{ old('role', $user->role) == 'doctor' ? 'selected' : '' }}>Usuário Médico</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('status') border-red-300 @enderror" required>
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                                <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="mb-8 {{ old('role', $user->role ?? 'user') === 'admin' ? 'hidden' : '' }}" id="modules-section">
                    <input type="hidden" name="modules_present" value="1">
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
                            $availableModules = App\Models\Tenant\Module::available();
                            $modules = collect($availableModules)->reject(function($module) {
                                return in_array($module['key'], ['users', 'settings']);
                            })->values()->all();
                            $oldModules = old('modules', $user->modules ?? []);
                        @endphp
                        <div class="flex flex-wrap gap-2 mb-4">
                            <button type="button" class="btn-patient-secondary" data-modules-select-all>Selecionar todos</button>
                            <button type="button" class="btn-patient-secondary" data-modules-clear>Limpar seleção</button>
                        </div>
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
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
