@extends('layouts.tailadmin.public')

@section('title', 'Cadastro de Paciente — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')


@section('content')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <h4 class="card-title mb-1">
                                            <i class="mdi mdi-account-plus text-primary me-2"></i>
                                            Novo Cadastro
                                        </h4>
                                        <p class="card-description mb-0 text-muted">Preencha os dados abaixo para se cadastrar na clínica</p>
                                    </div>
                                </div>

                                {{-- Mensagens --}}
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="mdi mdi-check-circle me-2"></i>
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <form class="forms-sample" action="{{ route('public.patient.register.submit', ['slug' => $tenant->subdomain]) }}" method="POST">
                                    @csrf

                                    {{-- Seção: Dados Pessoais --}}
                                    <div class="mb-4">
                                        <h5 class="mb-3 text-primary">
                                            <i class="mdi mdi-account-outline me-2"></i>
                                            Dados Pessoais
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-account me-1"></i>
                                                        Nome Completo <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                                           name="full_name" value="{{ old('full_name') }}" 
                                                           placeholder="Digite seu nome completo" required>
                                                    @error('full_name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-card-account-details me-1"></i>
                                                        CPF <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                                           name="cpf" id="cpf" data-mask="cpf" value="{{ old('cpf') }}" 
                                                           maxlength="14" placeholder="000.000.000-00" required>
                                                    @error('cpf')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-calendar me-1"></i>
                                                        Data de Nascimento
                                                    </label>
                                                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                                           name="birth_date" value="{{ old('birth_date') }}"
                                                           max="{{ date('Y-m-d') }}">
                                                    @error('birth_date')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Seção: Contato --}}
                                    <div class="mb-4">
                                        <h5 class="mb-3 text-primary">
                                            <i class="mdi mdi-phone me-2"></i>
                                            Informações de Contato
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-email me-1"></i>
                                                        E-mail
                                                    </label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                           name="email" value="{{ old('email') }}" 
                                                           placeholder="exemplo@email.com">
                                                    @error('email')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-semibold">
                                                        <i class="mdi mdi-phone me-1"></i>
                                                        Telefone
                                                    </label>
                                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                           name="phone" id="phone" data-mask="phone" value="{{ old('phone') }}" 
                                                           maxlength="20" placeholder="(00) 00000-0000">
                                                    @error('phone')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Botões de Ação --}}
                                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t">
                                        <x-tailadmin-button variant="secondary" size="md" href="{{ route('public.patient.identify', ['slug' => $tenant->subdomain]) }}"
                                            class="w-full sm:w-auto max-w-[220px] justify-center bg-transparent border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5">
                                            <i class="mdi mdi-arrow-left"></i>
                                            Cancelar
                                        </x-tailadmin-button>
                                        <x-tailadmin-button type="submit" variant="primary" size="md" class="w-full sm:w-auto max-w-[220px] justify-center">
                                            <i class="mdi mdi-content-save"></i>
                                            Cadastrar
                                        </x-tailadmin-button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
