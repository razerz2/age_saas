@extends('layouts.tailadmin.public')

@section('title', 'Identificação — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-8">
            <div class="pt-10 pb-6 sm:pt-12 sm:pb-8 lg:pt-14 text-center">
                <div class="mx-auto mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100">
                    <i class="mdi mdi-account-search text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Identificação do Paciente</h1>
                <p class="mt-2 text-sm text-slate-600">Informe seu CPF ou E-mail para continuar com o agendamento.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if (session('success') && session('patient_found'))
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            <div class="flex items-start gap-2">
                                <i class="mdi mdi-check-circle text-base text-emerald-600"></i>
                                <div>
                                    <p class="font-semibold">Paciente identificado com sucesso!</p>
                                    <p class="mt-1">Olá, <strong>{{ session('patient_name') }}</strong>!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif (session('success'))
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            <div class="flex items-start gap-2">
                                <i class="mdi mdi-check-circle text-base text-emerald-600"></i>
                                <p>{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-900">
                            <div class="flex items-start gap-2">
                                <i class="mdi mdi-alert-circle text-base text-red-600"></i>
                                <div>
                                    <p class="font-semibold">Erros encontrados</p>
                                    <div class="mt-2 space-y-1 text-sm text-red-800">
                                        @foreach ($errors->all() as $error)
                                            <p>{{ $error }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.patient.identify.submit', ['slug' => $tenant->subdomain]) }}" class="px-6 py-6">
                    @csrf

                    <div>
                        <label for="identifier" class="mb-1.5 block text-sm font-medium text-slate-700">
                            <span class="inline-flex items-center gap-1">
                                <i class="mdi mdi-card-account-details text-slate-500 text-base"></i>
                                <span>CPF ou E-mail</span>
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input
                            type="text"
                            id="identifier"
                            data-mask="cpf"
                            name="identifier"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('identifier') border-red-300 focus:ring-red-500 @enderror"
                            placeholder="000.000.000-00 ou seu@email.com"
                            value="{{ old('identifier') }}"
                            required
                            autofocus
                            autocomplete="off"
                        >
                        @error('identifier')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">Informe seu CPF ou e-mail cadastrado na clínica.</p>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <button
                            type="submit"
                            class="inline-flex w-auto min-w-[180px] items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        >
                            <i class="mdi mdi-arrow-right text-lg text-slate-900"></i>
                            Continuar
                        </button>
                    </div>
                </form>

                @if (session('patient_not_found') || ($errors->has('identifier') && old('identifier')))
                    <div class="border-t border-slate-200 px-6 py-6 text-center">
                        <div class="mx-auto max-w-xl rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <div class="flex items-start justify-center gap-2">
                                <i class="mdi mdi-alert-circle text-base text-amber-600"></i>
                                <p class="font-semibold">Você ainda não possui cadastro na clínica.</p>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-center">
                            <a
                                href="{{ route('public.patient.register', ['slug' => $tenant->subdomain]) }}"
                                class="inline-flex w-auto min-w-[180px] items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            >
                                <i class="mdi mdi-account-plus text-lg text-slate-900"></i>
                                Criar Cadastro
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="pt-8 text-center">
                <p class="text-xs text-slate-500">
                    {{ date('Y') }} {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
@endsection

