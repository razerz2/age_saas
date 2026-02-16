@extends('layouts.tailadmin.public')

@section('title', 'Login - Portal do Paciente')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gray-50">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <img src="{{ asset('tailadmin/assets/images/logo/logo.svg') }}" alt="Logo" class="mx-auto h-10 w-auto mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Portal do Paciente</h1>
                <p class="mt-1 text-sm text-gray-600">Entre para acessar seus agendamentos e notificações.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('patient.login.submit', ['slug' => $tenant->subdomain ?? $tenant]) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="seu@email.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Manter conectado</span>
                        </label>

                        <a href="{{ route('patient.forgot-password', ['slug' => $tenant->subdomain ?? $tenant]) }}" class="font-medium text-blue-600 hover:text-blue-700">
                            Esqueceu a senha?
                        </a>
                    </div>

                    <div class="pt-2">
                        <x-tailadmin-button type="submit" variant="primary" size="lg" class="w-full justify-center">
                            Entrar
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

