@extends('layouts.tailadmin.public')

@section('title', 'Identificação — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="max-w-lg w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('tailadmin/src/logo.svg') }}" alt="Logo" class="mx-auto h-12 w-auto">
        </div>

        <!-- Formulário de Identificação -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Identificação do Paciente</h2>
                <p class="text-sm text-gray-500 mt-2">Informe seu CPF ou E-mail para continuar com o agendamento</p>
            </div>

            <!-- Mensagem de Sucesso quando paciente encontrado -->
            @if (session('success') && session('patient_found'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Paciente identificado com sucesso!</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Olá, <strong>{{ session('patient_name') }}</strong>! O sistema de agendamento público será implementado em breve.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-green-700">
                                <p>{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mensagem de Erro Geral -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414-1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erros encontrados</h3>
                            <div class="mt-2 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Identificação -->
            <form method="POST" action="{{ route('public.patient.identify.submit', ['slug' => $tenant->subdomain]) }}">
                @csrf

                <!-- Identificador (CPF ou Email) -->
                <div class="mb-6">
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">CPF ou E-mail</label>
                    <input 
                        type="text" 
                        id="identifier" data-mask="cpf"
                        name="identifier"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('identifier') border-red-300 @enderror"
                        placeholder="000.000.000-00 ou seu@email.com" 
                        value="{{ old('identifier') }}" 
                        required 
                        autofocus
                        autocomplete="off">
                    @error('identifier')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informe seu CPF ou e-mail cadastrado na clínica
                    </p>
                </div>

                <!-- Botão Submit -->
                <button type="submit" class="w-full bg-brand-600 text-white py-2 px-4 rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition duration-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    Continuar
                </button>
            </form>

            <!-- Mensagem quando paciente não encontrado -->
            @if (session('patient_not_found') || ($errors->has('identifier') && old('identifier')))
                <div class="mt-6 text-center">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Você ainda não possui cadastro na clínica.</h3>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('public.patient.register', ['slug' => $tenant->subdomain]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Criar Cadastro
                    </a>
                </div>
            @endif

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-xs text-gray-500">
                    {{ date('Y') }} {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
</div>


@endsection
