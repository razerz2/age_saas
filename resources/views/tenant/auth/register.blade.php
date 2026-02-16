@extends('layouts.tailadmin.auth')

@section('title', 'Registrar — Sistema')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('tailadmin/src/logo.svg') }}" alt="Logo" class="mx-auto h-12 w-auto">
        </div>

        <!-- Formulário de Registro -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Crie sua conta</h2>
                <p class="text-sm text-gray-500 mt-1">Leva apenas alguns segundos.</p>
            </div>

            <!-- Form de Registro -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome completo</label>
                    <input type="text" name="name" id="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name') border-red-300 @enderror"
                        placeholder="Nome completo" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                    <input type="email" name="email" id="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('email') border-red-300 @enderror"
                        placeholder="E-mail" value="{{ old('email') }}" required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('password') border-red-300 @enderror"
                        placeholder="Senha" required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirm -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirme a senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        placeholder="Confirme a senha" required>
                </div>

                <!-- Termos -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="terms" id="terms" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded" required>
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            Eu concordo com os Termos & Condições
                        </label>
                    </div>
                </div>

                <!-- Botão Registrar -->
                <button type="submit" class="w-full bg-brand-600 text-white py-2 px-4 rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition duration-200">
                    Registrar
                </button>

                <!-- Login -->
                <div class="mt-6 text-center">
                    <span class="text-sm text-gray-600">Já possui conta?</span>
                    <a href="{{ route('login') }}" class="ml-1 text-sm text-brand-600 hover:text-brand-500">Entrar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
