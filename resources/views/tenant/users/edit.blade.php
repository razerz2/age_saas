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
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.users.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Usuários</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
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
                <x-icon name="check-circle-outline" size="text-lg" class="text-green-600 dark:text-green-400" />
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
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
                        <x-icon name="pencil-outline" size="text-xl" class="text-blue-600" />
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
                        <x-icon name="account-outline" size="text-lg" class="mr-2 text-blue-600" />
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
                                <x-icon name="camera-outline" size="text-lg" class="mr-2" />
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
                        <x-icon name="email-outline" size="text-lg" class="mr-2 text-blue-600" />
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
                        <x-icon name="cog-outline" size="text-lg" class="mr-2 text-blue-600" />
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
                        <x-icon name="view-grid-outline" size="text-lg" class="mr-2 text-blue-600 dark:text-blue-400" />
                        Módulos
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione os módulos disponíveis para este usuário:</label>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <x-icon name="information-outline" size="text-lg" class="text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0 mt-0.5" />
                                <div class="text-sm text-blue-800 dark:text-blue-200">
                                    <p id="modules-info-text">
                                        Nota: Os módulos foram pré-selecionados conforme as configurações padrão para usuários comuns em Configurações → Usuários & Permissões. Você pode ajustar manualmente se necessário.
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
                    <a href="{{ workspace_route('tenant.users.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" size="text-sm" />
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" size="text-sm" />
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
