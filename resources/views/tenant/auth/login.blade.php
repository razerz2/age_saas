@extends('layouts.tailadmin.auth')

@section('title', 'Login — Sistema')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('tailadmin/assets/images/logo/logo.svg') }}" alt="Logo" class="mx-auto h-12 w-auto">
        </div>

        @if (!$tenant)
            <!-- Mensagem de erro quando tenant não existe -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/20 mb-6">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Clínica não encontrada</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">A clínica informada não existe ou não está disponível</p>
                    
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Erro!</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>{{ $error_message ?? 'A clínica informada não existe ou não está disponível. Verifique o endereço e tente novamente.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">Verifique se o endereço está correto e tente novamente.</p>
                </div>
            </div>
        @else
            <!-- Formulário de Login -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold mb-2" style="color: #111827;">Bem-vindo!</h2>
                    <p class="text-sm mb-2" style="color: #6b7280;">Tenant: {{ $tenant->subdomain }}</p>
                    <p class="text-sm mt-1" style="color: #6b7280;">Entre para continuar</p>
                </div>

                <!-- Alerta de erro 419 -->
                @if (session('error'))
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Atenção!</h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if ($errors->has('_token'))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Erro!</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>Token de segurança inválido. Por favor, recarregue a página e tente novamente.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('tenant.login.submit', ['slug' => $tenant->subdomain]) }}" id="login-form">
                    @csrf

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-semibold mb-2" style="color: #374151;">E-mail</label>
                        <div class="relative">
                            <input type="email" name="email" id="email"
                                class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="border-color: #d1d5db; background-color: white; color: #111827;"
                            onfocus="this.style.borderColor='#2563eb'; this.style.outline='none'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                            onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                                placeholder="seu@email.com" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div class="mb-8">
                        <label for="password" class="block text-sm font-semibold mb-2" style="color: #374151;">Senha</label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="border-color: #d1d5db; background-color: white; color: #111827;"
                            onfocus="this.style.borderColor='#2563eb'; this.style.outline='none'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                            onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                                placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Botão Login -->
                    <button type="submit" class="w-full font-semibold py-3 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-200 transform hover:scale-[1.02] flex items-center justify-center gap-2"
                            style="background-color: #2563eb; color: white; border: none;"
                            onmouseover="this.style.backgroundColor='#1d4ed8'"
                            onmouseout="this.style.backgroundColor='#2563eb'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Entrar
                    </button>

                    <!-- Manter conectado + Esqueceu a senha -->
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                            <label for="remember" class="ml-2 block text-sm" style="color: #374151;">
                                Manter conectado
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="#" class="text-sm font-medium" style="color: #2563eb;">
                                Esqueceu a senha?
                            </a>
                        @endif
                    </div>

                    <!-- Criar conta -->
                    @if (Route::has('register'))
                        <div class="mt-8 text-center pt-6 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-sm" style="color: #6b7280;">Não tem uma conta?</span>
                            <a href="#" class="ml-1 text-sm font-semibold" style="color: #2563eb;">Criar conta</a>
                        </div>
                    @endif
                </form>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    {{-- Script para prevenir erro 419 --}}
    @if ($tenant)
    <script>
        (function() {
            // Atualizar token CSRF periodicamente (a cada 4 minutos)
            setInterval(function() {
                fetch('{{ route("tenant.login", ["slug" => $tenant->subdomain]) }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    }
                    throw new Error('Resposta não OK');
                })
                .then(html => {
                    try {
                        // Extrair novo token CSRF do HTML
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newToken = doc.querySelector('input[name="_token"]')?.value;
                        
                        if (newToken) {
                            // Atualizar token no formulário
                            const formToken = document.querySelector('#login-form input[name="_token"]');
                            if (formToken && formToken.value !== newToken) {
                                formToken.value = newToken;
                                console.log('Token CSRF atualizado automaticamente');
                            }
                        }
                    } catch (e) {
                        // Ignorar erros de parsing silenciosamente
                    }
                })
                .catch(function() {
                    // Ignorar erros silenciosamente
                });
            }, 4 * 60 * 1000); // 4 minutos (antes dos 120 minutos padrão de expiração)
        })();
    </script>
    @endif
@endpush

@endsection
