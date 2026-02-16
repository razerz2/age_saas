@extends('layouts.tailadmin.public')

@section('title', 'Recuperar Senha - Portal do Paciente')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gray-50">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Recuperar Senha</h1>
                <p class="mt-1 text-sm text-gray-600">Informe seu e-mail para receber o link de redefinição.</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="GET" action="#" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="seu@email.com"
                        >
                    </div>

                    <div class="pt-2">
                        <x-tailadmin-button type="submit" variant="primary" size="lg" class="w-full justify-center">
                            Enviar link de recuperação
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
