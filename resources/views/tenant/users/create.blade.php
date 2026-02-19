@extends('layouts.tailadmin.app')

@section('title', 'Criar Usuário')
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
    data-initial-role="{{ old('role', 'user') }}">
</div>

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.users.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Usuários</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="users" />
            </div>
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-icon name="account-plus-outline" size="text-xl" class="text-blue-600 dark:text-blue-400" />
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
                        <x-icon name="account-outline" size="text-lg" class="mr-2 text-blue-600 dark:text-blue-400" />
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
                                <x-icon name="camera-outline" size="text-lg" class="mr-2" />
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
                                        <x-icon name="check-circle-outline" size="text-base" class="mr-1" />
                                        <span id="avatar-filename"></span>
                                    </p>
                                    <button type="button" id="avatar-remove" class="mt-2 px-3 py-1.5 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">
                                        <x-icon name="trash-can-outline" size="text-base" class="mr-1" />
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
                        <x-icon name="email-outline" size="text-lg" class="mr-2 text-blue-600 dark:text-blue-400" />
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
                                <button type="button" data-toggle-password-target="password" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    title="Mostrar/Ocultar senha">
                                    <x-icon name="eye-outline" id="password-eye-icon" size="text-lg" />
                                </button>
                                <button type="button" data-generate-password="password" data-generate-password-confirm="password_confirmation" 
                                    class="shrink-0 w-24 inline-flex items-center justify-center gap-1.5 px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-r-lg">
                                    <x-icon name="refresh" size="text-lg" />
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
                                <button type="button" data-toggle-password-target="password_confirmation" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    title="Mostrar/Ocultar senha">
                                    <x-icon name="eye-outline" id="password_confirmation-eye-icon" size="text-lg" />
                                </button>
                                <div class="shrink-0 w-24 inline-flex items-center justify-center px-0 py-2.5 border-t border-r border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-400 dark:text-gray-300 rounded-r-lg">
                                    <x-icon name="check-circle-outline" size="text-lg" class="opacity-0" />
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
                        <x-icon name="cog-outline" size="text-lg" class="mr-2 text-blue-600 dark:text-blue-400" />
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
                        <x-icon name="stethoscope" size="text-lg" class="mr-2 text-blue-600 dark:text-blue-400" />
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
                <div class="mb-8 {{ old('role', 'user') === 'admin' ? 'hidden' : '' }}" id="modules-section">
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
                        <div class="flex flex-wrap gap-2 mb-4">
                            <button type="button" class="btn-patient-secondary" data-modules-select-all>Selecionar todos</button>
                            <button type="button" class="btn-patient-secondary" data-modules-clear>Limpar seleção</button>
                        </div>
                        @php
                            // Buscar apenas módulos disponíveis (no plano e habilitados na tenant)
                            $availableModules = App\Models\Tenant\Module::available();
                            // Sempre remover módulo "usuários" - apenas admins têm acesso, mas não podem atribuir a outros
                                    $modules = collect($availableModules)->reject(function($module) {
                                return in_array($module['key'], ['users', 'settings']);
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
                    <a href="{{ workspace_route('tenant.users.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" size="text-sm" />
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" size="text-sm" />
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
                    <x-icon name="camera-outline" size="text-lg" class="mr-2" />
                    Capturar Foto
                </h3>
                <button data-webcam-close="true" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                    <x-icon name="close" size="text-lg" />
                </button>
            </div>
            <div class="mt-4">
                <video id="webcam-video" autoplay playsinline class="w-full rounded-lg hidden"></video>
                <canvas id="webcam-canvas" class="hidden"></canvas>
                <div id="webcam-placeholder" class="p-8 text-center">
                    <x-icon name="camera-outline" size="text-5xl" class="text-gray-400 dark:text-gray-500" />
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Clique em "Iniciar Webcam" para começar</p>
                </div>
            </div>
            <div class="mt-6 flex justify-center space-x-6">
                <button type="button" id="webcam-start" class="webcam-action-button px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <x-icon name="video-outline" size="text-lg" class="mr-2" />
                    Iniciar Webcam
                </button>
                <button type="button" id="webcam-capture" class="webcam-action-button px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 hidden">
                    <x-icon name="camera-outline" size="text-lg" class="mr-2" />
                    Capturar Foto
                </button>
                <button type="button" id="webcam-stop" class="webcam-action-button px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 hidden">
                    <x-icon name="stop-circle-outline" size="text-lg" class="mr-2" />
                    Parar
                </button>
                <button type="button" data-webcam-close="true" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Cancelar
                </button>
            </div>
        </div>
    </div>



@endsection
