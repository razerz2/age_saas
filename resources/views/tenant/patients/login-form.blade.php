@extends('layouts.tailadmin.app')

@section('title', 'Gerenciar Login do Paciente')

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
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
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
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
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
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
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
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
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
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                        title="Gerar senha aleatória">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
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
                            <a href="{{ workspace_route('tenant.patients.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gerador de senha
        const generatePasswordBtn = document.getElementById('generatePassword');
        if (generatePasswordBtn) {
            generatePasswordBtn.addEventListener('click', function() {
                const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const lowercase = 'abcdefghijklmnopqrstuvwxyz';
                const numbers = '0123456789';
                const symbols = '!@#$%&*';
                const allChars = uppercase + lowercase + numbers + symbols;
                
                let password = '';
                // Garantir pelo menos um de cada tipo
                password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
                password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
                password += numbers.charAt(Math.floor(Math.random() * numbers.length));
                password += symbols.charAt(Math.floor(Math.random() * symbols.length));
                
                // Completar até 12 caracteres
                for (let i = password.length; i < 12; i++) {
                    password += allChars.charAt(Math.floor(Math.random() * allChars.length));
                }
                
                // Embaralhar a senha
                password = password.split('').sort(() => Math.random() - 0.5).join('');
                
                document.getElementById('password').value = password;
                const confirmField = document.getElementById('password_confirmation');
                if (confirmField) {
                    confirmField.value = password;
                }
                
                // Mostrar senha temporariamente
                const passwordField = document.getElementById('password');
                const wasPassword = passwordField.type === 'password';
                if (wasPassword) {
                    passwordField.type = 'text';
                    setTimeout(() => {
                        passwordField.type = 'password';
                    }, 5000);
                }
            });
        }

        // Validação de confirmação de senha
        @if(!$patient->login)
        function validatePasswordConfirmation() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            
            if (password && confirmation) {
                if (password !== confirmation) {
                    document.getElementById('password_confirmation').classList.add('border-red-500');
                    const existingError = document.getElementById('password-confirmation-error');
                    if (!existingError) {
                        const errorDiv = document.createElement('p');
                        errorDiv.id = 'password-confirmation-error';
                        errorDiv.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        errorDiv.textContent = 'As senhas não coincidem.';
                        document.getElementById('password_confirmation').parentNode.appendChild(errorDiv);
                    }
                    return false;
                } else {
                    document.getElementById('password_confirmation').classList.remove('border-red-500');
                    const existingError = document.getElementById('password-confirmation-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    return true;
                }
            }
            return true;
        }

        document.getElementById('password').addEventListener('keyup', validatePasswordConfirmation);
        document.getElementById('password_confirmation').addEventListener('keyup', validatePasswordConfirmation);

        // Validar antes de enviar formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!validatePasswordConfirmation()) {
                e.preventDefault();
                showAlert({ type: 'warning', title: 'Atenção', message: 'As senhas não coincidem. Por favor, verifique.' });
                return false;
            }
        });
        @endif
    });
</script>
@endpush

