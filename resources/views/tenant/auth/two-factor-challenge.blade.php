@extends('layouts.tailadmin.auth')

@section('title', 'Verificação 2FA — Sistema')
@section('page', 'auth')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('tailadmin/src/logo.svg') }}" alt="Logo" class="mx-auto h-12 w-auto">
        </div>

        <!-- Formulário de Verificação 2FA -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Verificação de Dois Fatores</h2>
                @if(isset($method) && in_array($method, ['email', 'whatsapp']))
                    <p class="text-sm text-gray-500 mt-1">Digite o código de 6 dígitos enviado via {{ $method === 'email' ? 'e-mail' : 'WhatsApp' }}.</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Código enviado!</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Verifique seu {{ $method === 'email' ? 'e-mail' : 'WhatsApp' }}.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 mt-1">Digite o código de 6 dígitos do seu aplicativo autenticador.</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Dica:</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Você também pode usar um código de recuperação se não tiver acesso ao seu dispositivo autenticador.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erros encontrados</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Verificação 2FA -->
            <form method="POST" action="{{ route('tenant.two-factor.challenge', ['slug' => $tenant->subdomain]) }}" id="two-factor-form">
                @csrf

                <!-- Código -->
                <div class="mb-6">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Código de Verificação</label>
                    <input type="text" 
                           name="code" 
                           id="code"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 auth-code-input @error('code') border-red-300 @enderror"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           required 
                           autofocus
                           autocomplete="one-time-code">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Digite o código de 6 dígitos do seu aplicativo autenticador ou um código de recuperação.
                    </p>
                </div>

                <!-- Botão Verificar -->
                <button type="submit" class="w-full bg-brand-600 text-white py-2 px-4 rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition duration-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Verificar e Continuar
                </button>

                <!-- Reenviar / Voltar ao Login -->
                <div class="mt-6 text-center">
                    @if(isset($method) && in_array($method, ['email', 'whatsapp']))
                        <form method="POST" action="{{ route('tenant.two-factor.challenge.resend', ['slug' => $tenant->subdomain]) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-brand-600 hover:text-brand-500 flex items-center justify-center mx-auto">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reenviar código
                            </button>
                        </form>
                        <span class="text-gray-400 mx-2">|</span>
                    @endif
                    <a href="{{ route('tenant.login', ['slug' => $tenant->subdomain]) }}" class="text-sm text-brand-600 hover:text-brand-500 flex items-center justify-center mx-auto">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar ao login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
