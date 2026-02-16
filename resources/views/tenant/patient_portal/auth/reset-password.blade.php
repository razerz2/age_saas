@extends('layouts.tailadmin.public')

@section('title', 'Redefinir Senha - Portal do Paciente')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gray-50">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Redefinir Senha</h1>
                <p class="mt-1 text-sm text-gray-600">Defina uma nova senha para sua conta.</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="POST" action="#" class="space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token ?? '' }}">

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Nova senha</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="pt-2">
                        <x-tailadmin-button type="submit" variant="primary" size="lg" class="w-full justify-center">
                            Redefinir senha
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
