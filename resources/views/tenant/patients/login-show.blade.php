@extends('layouts.tailadmin.app')

@section('title', 'Informações de Login do Paciente')
@section('page', 'patients')

@section('content')

    <div id="patients-login-show-config" data-page-config="true"></div>

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
                        <span class="text-gray-900 dark:text-white font-semibold">Informações de Login</span>
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
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefone</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $patient->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Informações de Acesso -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="mdi-key-outline" size="text-lg" class="mr-2 text-blue-600" />
                        Informações de Acesso ao Portal
                    </h4>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                        <h5 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center">
                            <x-icon name="mdi-information-outline" size="text-sm" class="mr-2" />
                            Credenciais de Acesso
                        </h5>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">URL do Portal</label>
                                <div class="flex gap-2 mt-2">
                                    <input type="text" 
                                           class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" 
                                           id="portalUrl" 
                                           value="{{ route('patient.login', ['slug' => \Spatie\Multitenancy\Models\Tenant::current()->subdomain ?? 'tenant']) }}" 
                                           readonly>
                                    <button type="button" 
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                            data-copy-target="portalUrl" 
                                            title="Copiar URL">
                                        <x-icon name="mdi-content-copy" size="text-sm" />
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">E-mail</label>
                                <div class="flex gap-2 mt-2">
                                    <input type="text" 
                                           class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" 
                                           id="loginEmail" 
                                           value="{{ $patient->login->email }}" 
                                           readonly>
                                    <button type="button" 
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                            data-copy-target="loginEmail" 
                                            title="Copiar e-mail">
                                        <x-icon name="mdi-content-copy" size="text-sm" />
                                    </button>
                                </div>
                            </div>

                            @if(session('password'))
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Senha</label>
                                    <div class="flex gap-2 mt-2">
                                        <input type="password" 
                                               class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" 
                                               id="loginPassword" 
                                               value="{{ session('password') }}" 
                                               readonly>
                                        <button type="button" 
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                                data-toggle-password-target="loginPassword" 
                                                title="Mostrar/Ocultar senha">
                                            <x-icon name="mdi-eye-outline" size="text-sm" id="toggleIcon" />
                                        </button>
                                        <button type="button" 
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                                data-copy-target="loginPassword" 
                                                title="Copiar senha">
                                            <x-icon name="mdi-content-copy" size="text-sm" />
                                        </button>
                                    </div>
                                    <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
                                        <x-icon name="mdi-alert" size="text-sm" class="inline mr-1" />
                                        Anote esta senha! Ela não será exibida novamente.
                                    </p>
                                </div>
                            @else
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Senha</label>
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 mt-2">
                                        <div class="flex items-center text-sm text-yellow-800 dark:text-yellow-200">
                                            <x-icon name="mdi-lock-outline" size="text-sm" class="mr-2" />
                                            A senha não pode ser visualizada por questões de segurança.
                                        </div>
                                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">Para redefinir a senha, edite o login do paciente.</p>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                @if($patient->login->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                        <x-icon name="mdi-check-circle-outline" size="text-xs" class="mr-1" />
                                        Acesso Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                        <x-icon name="mdi-alert" size="text-xs" class="mr-1" />
                                        Acesso Bloqueado
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Envio de Informações -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="mdi-email-outline" size="text-lg" class="mr-2 text-blue-600" />
                    Enviar Informações de Acesso
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Envie as credenciais de acesso ao paciente por e-mail ou WhatsApp.</p>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap gap-3">
                    <form id="sendEmailForm" 
                          action="{{ workspace_route('tenant.patients.login.send-email', $patient->id) }}" 
                          method="POST" 
                          class="inline">
                        @csrf
                        @if(session('password'))
                            <input type="hidden" name="password" value="{{ session('password') }}">
                        @endif
                        <button type="submit" 
                                class="btn btn-primary inline-flex items-center"
                                data-confirm-submit="true"
                                data-confirm-title="Enviar por e-mail"
                                data-confirm-message="Deseja enviar as informações de acesso por e-mail?"
                                data-confirm-confirm-text="Enviar"
                                data-confirm-cancel-text="Cancelar"
                                data-confirm-type="warning">
                            <x-icon name="mdi-email-outline" size="text-sm" class="mr-2" />
                            Enviar por E-mail
                        </button>
                    </form>

                    @if($patient->phone)
                        <form id="sendWhatsAppForm" 
                              action="{{ workspace_route('tenant.patients.login.send-whatsapp', $patient->id) }}" 
                              method="POST" 
                              class="inline">
                            @csrf
                            @if(session('password'))
                                <input type="hidden" name="password" value="{{ session('password') }}">
                            @endif
                            <button type="submit" 
                                    class="btn btn-primary inline-flex items-center"
                                    data-confirm-submit="true"
                                    data-confirm-title="Enviar por WhatsApp"
                                    data-confirm-message="Deseja enviar as informações de acesso por WhatsApp?"
                                    data-confirm-confirm-text="Enviar"
                                    data-confirm-cancel-text="Cancelar"
                                    data-confirm-type="warning">
                                <x-icon name="mdi-whatsapp" size="text-sm" class="mr-2" />
                                Enviar por WhatsApp
                            </button>
                        </form>
                    @else
                        <button type="button" 
                                disabled 
                                class="btn btn-outline opacity-60 cursor-not-allowed inline-flex items-center"
                                title="Paciente não possui telefone cadastrado">
                            <x-icon name="mdi-whatsapp" size="text-sm" class="mr-2" />
                            Enviar por WhatsApp
                        </button>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Adicione um telefone ao paciente para enviar por WhatsApp.</p>
                    @endif

                    <a href="{{ workspace_route('tenant.patients.index') }}" 
                       class="btn btn-outline inline-flex items-center">
                        <x-icon name="mdi-arrow-left" size="text-sm" class="mr-2" />
                        Voltar para Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
