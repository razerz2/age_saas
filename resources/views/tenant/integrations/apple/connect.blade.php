@extends('layouts.tailadmin.app')

@section('title', 'Conectar Apple Calendar')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <x-icon name="apple" class=" text-primary me-2" />
            Conectar Apple Calendar
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.integrations.index') }}">Integrações</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.integrations.apple.index') }}">Apple Calendar</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Conectar</li>
            </ol>
        </nav>
    </div>

    {{-- Alertas --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <x-icon name="alert-circle" class=" me-1" /> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <x-icon name="apple" class=" text-primary me-2" />
                        Conectar Apple Calendar (iCloud) - {{ $doctor->user->name_full ?? $doctor->user->name }}
                    </h4>

                    {{-- Instruções --}}
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">
                            <x-icon name="information-outline" class=" me-2" />
                            Como obter suas credenciais do iCloud
                        </h5>
                        <ol class="mb-0">
                            <li class="mb-2">
                                Acesse <a href="https://appleid.apple.com/account/manage" target="_blank" rel="noopener noreferrer">appleid.apple.com</a> e faça login com sua conta Apple
                            </li>
                            <li class="mb-2">
                                Na seção "Segurança", encontre "Senhas de app" e clique em "Gerar senha de app"
                            </li>
                            <li class="mb-2">
                                Dê um nome para a senha (ex: "Agendamento SaaS") e clique em "Criar"
                            </li>
                            <li class="mb-2">
                                Copie a senha gerada (ela só será exibida uma vez) e use no formulário abaixo
                            </li>
                            <li>
                                Use seu email do iCloud como usuário e a senha de app gerada como senha
                            </li>
                        </ol>
                    </div>

                    <form action="{{ workspace_route('tenant.integrations.apple.connect', $doctor->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                Email do iCloud <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   placeholder="seu.email@icloud.com" 
                                   required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                O email da sua conta Apple/iCloud
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Senha de App <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="xxxx-xxxx-xxxx-xxxx" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                A senha de app gerada em appleid.apple.com (não use a senha da sua conta)
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="server_url" class="form-label">
                                URL do Servidor CalDAV
                            </label>
                            <input type="url" 
                                   class="form-control @error('server_url') is-invalid @enderror" 
                                   id="server_url" 
                                   name="server_url" 
                                   value="{{ old('server_url', 'https://caldav.icloud.com') }}" 
                                   placeholder="https://caldav.icloud.com">
                            @error('server_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                URL do servidor CalDAV. Para iCloud, use: https://caldav.icloud.com
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="calendar_url" class="form-label">
                                URL do Calendário (Opcional)
                            </label>
                            <input type="text" 
                                   class="form-control @error('calendar_url') is-invalid @enderror" 
                                   id="calendar_url" 
                                   name="calendar_url" 
                                   value="{{ old('calendar_url') }}" 
                                   placeholder="/calendars/seu-email@icloud.com/">
                            @error('calendar_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Deixe em branco para descobrir automaticamente. Se souber o caminho específico do calendário, pode informar aqui.
                            </small>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.integrations.apple.index') }}"
                                class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <x-icon name="arrow-left" class="" />
                                Cancelar
                            </x-tailadmin-button>
                            <x-tailadmin-button type="submit" variant="primary">
                                <x-icon name="check" class="" />
                                Conectar
                            </x-tailadmin-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

