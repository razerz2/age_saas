@extends('layouts.tailadmin.public')

@section('title', 'Cadastro de Paciente — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-8">
            <div class="pt-10 pb-6 sm:pt-12 sm:pb-8 lg:pt-14 text-center">
                <div class="mx-auto mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100">
                    <i class="mdi mdi-account-plus text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Novo Cadastro</h1>
                <p class="mt-2 text-sm text-slate-600">Preencha os dados abaixo para se cadastrar na clínica.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if (session('success'))
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
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('public.patient.register.submit', ['slug' => $tenant->subdomain]) }}" method="POST">
                    @csrf

                    <div class="px-6 py-5">
                        <h2 class="text-base font-semibold text-slate-900 flex items-center justify-center sm:justify-start gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <i class="mdi mdi-account-outline text-base"></i>
                            </span>
                            <span>Dados Pessoais</span>
                        </h2>
                        <p class="mt-1 text-xs sm:text-sm text-slate-600 text-center sm:text-left">Informe seus dados básicos para cadastro.</p>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="full_name">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-account text-slate-500 text-base"></i>
                                        <span>Nome Completo</span>
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="full_name"
                                    id="full_name"
                                    value="{{ old('full_name') }}"
                                    required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('full_name') border-red-300 focus:ring-red-500 @enderror"
                                    placeholder="Digite seu nome completo"
                                >
                                @error('full_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="cpf">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-card-account-details text-slate-500 text-base"></i>
                                        <span>CPF</span>
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="cpf"
                                    id="cpf"
                                    data-mask="cpf"
                                    value="{{ old('cpf') }}"
                                    maxlength="14"
                                    required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('cpf') border-red-300 focus:ring-red-500 @enderror"
                                    placeholder="000.000.000-00"
                                >
                                @error('cpf')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="birth_date">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-cake-variant text-slate-500 text-base"></i>
                                        <span>Data de Nascimento</span>
                                    </span>
                                </label>
                                <input
                                    type="date"
                                    name="birth_date"
                                    id="birth_date"
                                    value="{{ old('birth_date') }}"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('birth_date') border-red-300 focus:ring-red-500 @enderror"
                                >
                                @error('birth_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-5">
                        <h2 class="text-base font-semibold text-slate-900 flex items-center justify-center sm:justify-start gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <i class="mdi mdi-email-outline text-base"></i>
                            </span>
                            <span>Informações de Contato</span>
                        </h2>
                        <p class="mt-1 text-xs sm:text-sm text-slate-600 text-center sm:text-left">Como a clínica pode entrar em contato com você.</p>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="email">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-email text-slate-500 text-base"></i>
                                        <span>E-mail</span>
                                    </span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-300 focus:ring-red-500 @enderror"
                                    placeholder="exemplo@email.com"
                                >
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="phone">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-phone text-slate-500 text-base"></i>
                                        <span>Telefone</span>
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    data-mask="phone"
                                    value="{{ old('phone') }}"
                                    maxlength="20"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-300 focus:ring-red-500 @enderror"
                                    placeholder="(00) 00000-0000"
                                >
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <a
                                href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}"
                                class="inline-flex w-auto min-w-[180px] items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            >
                                <i class="mdi mdi-arrow-left text-lg text-slate-900"></i>
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="inline-flex w-auto min-w-[180px] items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            >
                                <i class="mdi mdi-content-save text-lg text-white"></i>
                                Cadastrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

