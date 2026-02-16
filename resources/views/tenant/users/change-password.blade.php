@extends('layouts.tailadmin.app')

@section('title', 'Alterar Senha')

@section('content')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Alterar Senha</h1>
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
                        <span class="ml-1 text-gray-500">Alterar Senha</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 max-w-2xl mx-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Alterar Senha</h2>
            <p class="text-sm text-gray-500 mt-1">Digite a senha atual e a nova senha.</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ workspace_route('tenant.users.change-password.store', $user->id) }}">
                @csrf

                <div class="space-y-6">
                    <!-- Senha Atual -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senha Atual</label>
                        <div class="relative w-full">
                            <div class="flex w-full">
                                <input type="password" name="current_password" id="current_password"
                                    class="w-full min-w-0 flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-white"
                                    required>
                                <button type="button" onclick="togglePasswordVisibility('current_password')" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    title="Mostrar/Ocultar senha">
                                    <svg class="w-4 h-4" id="current_password-eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <!-- Placeholder para manter largura igual ao campo com "Gerar" -->
                                <div class="shrink-0 w-24 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 rounded-r-md pointer-events-none select-none"
                                    aria-hidden="true">
                                    <svg class="w-4 h-4 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nova Senha -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nova Senha</label>
                        <div class="relative w-full">
                            <div class="flex w-full">
                                <input type="password" name="new_password" id="new_password"
                                    class="w-full min-w-0 flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-white"
                                    required>
                                <button type="button" onclick="togglePasswordVisibility('new_password')" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    title="Mostrar/Ocultar senha">
                                    <svg class="w-4 h-4" id="new_password-eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button type="button" onclick="generatePassword()" 
                                    class="shrink-0 w-24 inline-flex items-center justify-center gap-1.5 px-3 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-r-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Gerar
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mínimo 8 caracteres com maiúscula, minúscula, número e caractere especial</p>
                        @error('new_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmar Nova Senha -->
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmar Nova Senha</label>
                        <div class="relative w-full">
                            <div class="flex w-full">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                    class="w-full min-w-0 flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-white"
                                    required>
                                <button type="button" onclick="togglePasswordVisibility('new_password_confirmation')" 
                                    class="shrink-0 w-11 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    title="Mostrar/Ocultar senha">
                                    <svg class="w-4 h-4" id="new_password_confirmation-eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <!-- Placeholder para manter largura igual ao campo com "Gerar" -->
                                <div class="shrink-0 w-24 inline-flex items-center justify-center px-0 py-2 border-y border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 rounded-r-md pointer-events-none select-none"
                                    aria-hidden="true">
                                    <svg class="w-4 h-4 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Digite a senha novamente para confirmar</p>
                        @error('new_password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.users.index') }}" class="btn-patient-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        Alterar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('styles')
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
<script src="{{ asset('js/password-generator.js') }}"></script>
<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-eye-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('mdi-eye');
            icon.classList.add('mdi-eye-off');
        } else {
            field.type = 'password';
            icon.classList.remove('mdi-eye-off');
            icon.classList.add('mdi-eye');
        }
    }
    
    function generatePassword() {
        const password = generateStrongPassword();
        document.getElementById('new_password').value = password;
        document.getElementById('new_password_confirmation').value = password;
        
        // Mostra temporariamente
        document.getElementById('new_password').type = 'text';
        document.getElementById('new_password_confirmation').type = 'text';
        document.getElementById('new_password-eye-icon').classList.remove('mdi-eye');
        document.getElementById('new_password-eye-icon').classList.add('mdi-eye-off');
        document.getElementById('new_password_confirmation-eye-icon').classList.remove('mdi-eye');
        document.getElementById('new_password_confirmation-eye-icon').classList.add('mdi-eye-off');
        document.getElementById('new_password').select();
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            document.getElementById('new_password').type = 'password';
            document.getElementById('new_password_confirmation').type = 'password';
            document.getElementById('new_password-eye-icon').classList.remove('mdi-eye-off');
            document.getElementById('new_password-eye-icon').classList.add('mdi-eye');
            document.getElementById('new_password_confirmation-eye-icon').classList.remove('mdi-eye-off');
            document.getElementById('new_password_confirmation-eye-icon').classList.add('mdi-eye');
        }, 3000);
    }
</script>
@endpush

@endsection
