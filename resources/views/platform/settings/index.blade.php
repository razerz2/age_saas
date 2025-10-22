@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-dark font-weight-medium mb-1">Configurações do Sistema</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Configurações</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#geral" role="tab">Geral</a>
            </li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#integracoes" role="tab">Integrações</a>
            </li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#email" role="tab">E-mail</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sistema" role="tab">Sistema</a></li>
        </ul>

        <div class="tab-content p-4">
            {{-- Aba Geral --}}
            <div class="tab-pane fade show active" id="geral" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.general') }}">
                    @csrf
                    <div class="mb-3">
                        <label>Fuso Horário</label>
                        <select class="form-select" name="timezone">
                            @foreach (DateTimeZone::listIdentifiers() as $tz)
                                <option value="{{ $tz }}" {{ $settings['timezone'] == $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>País de preferência</label>
                        <select class="form-select" name="country_id">
                            <option value="">Selecione o país</option>
                            @foreach ($paises as $pais)
                                <option value="{{ $pais->id_pais }}"
                                    {{ $settings['country_id'] == $pais->id_pais ? 'selected' : '' }}>
                                    {{ $pais->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Idioma</label>
                        <select class="form-select" name="language">
                            <option value="pt_BR" {{ $settings['language'] == 'pt_BR' ? 'selected' : '' }}>Português
                                (Brasil)</option>
                            <option value="en_US" {{ $settings['language'] == 'en_US' ? 'selected' : '' }}>Inglês</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>

            {{-- Aba Integrações --}}
            <div class="tab-pane fade" id="integracoes" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.integrations') }}">
                    @csrf
                    <h5 class="mt-2">🔑 Asaas</h5>
                    <div class="mb-3">
                        <label>API URL</label>
                        <input type="text" class="form-control" name="ASAAS_API_URL" value="{{ env('ASAAS_API_URL') }}">
                    </div>
                    <div class="mb-3">
                        <label>API Key</label>
                        <input type="text" class="form-control" name="ASAAS_API_KEY"
                            value="{{ $settings['ASAAS_API_KEY'] }}">
                    </div>

                    <a href="{{ route('Platform.settings.test', 'asaas') }}" class="btn btn-secondary mb-4">
                        <i class="fas fa-plug me-1"></i> Testar Conexão ASAAS</a>

                    <h5>💬 Meta (WhatsApp)</h5>
                    <div class="mb-3">
                        <label>Access Token</label>
                        <input type="text" class="form-control" name="META_ACCESS_TOKEN"
                            value="{{ $settings['META_ACCESS_TOKEN'] }}">
                    </div>
                    <div class="mb-3">
                        <label>Phone Number ID</label>
                        <input type="text" class="form-control" name="META_PHONE_NUMBER_ID"
                            value="{{ $settings['META_PHONE_NUMBER_ID'] }}">
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('Platform.settings.test', 'meta') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-plug me-1"></i> Testar Conexão Meta
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Integrações
                        </button>
                    </div>
                </form>
            </div>

            {{-- Aba E-mail --}}
            <div class="tab-pane fade" id="email" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.integrations') }}">
                    @csrf
                    <h5>📧 Configuração de E-mail</h5>
                    <div class="mb-3"><label>Host</label><input type="text" name="MAIL_HOST" class="form-control"
                            value="{{ $settings['MAIL_HOST'] }}"></div>
                    <div class="mb-3"><label>Porta</label><input type="text" name="MAIL_PORT" class="form-control"
                            value="{{ $settings['MAIL_PORT'] }}"></div>
                    <div class="mb-3"><label>Usuário</label><input type="text" name="MAIL_USERNAME"
                            class="form-control" value="{{ $settings['MAIL_USERNAME'] }}"></div>
                    <div class="mb-3">
                        <label>Senha</label>
                        <input type="password" name="MAIL_PASSWORD" class="form-control" placeholder="••••••••"
                            value="{{ old('MAIL_PASSWORD') }}">
                        <small class="text-muted">A senha não é exibida por segurança. Reinsira se desejar alterar.</small>
                    </div>
                    <div class="mb-3"><label>Remetente</label><input type="email" name="MAIL_FROM_ADDRESS"
                            class="form-control" value="{{ $settings['MAIL_FROM_ADDRESS'] }}"></div>
                    <div class="mb-3"><label>Nome do Remetente</label><input type="text" name="MAIL_FROM_NAME"
                            class="form-control" value="{{ $settings['MAIL_FROM_NAME'] }}"></div>
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('Platform.settings.test', 'email') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-paper-plane me-1"></i> Testar Envio
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar E-mail
                        </button>
                    </div>
                </form>
            </div>

            {{-- Aba Sistema --}}
            <div class="tab-pane fade" id="sistema" role="tabpanel">
                <h5>🧩 Informações do Sistema</h5>
                <ul>
                    <li>Versão: <b>{{ systemVersion() }}</b></li>
                    <li>Ambiente: <b>{{ isProduction() ? 'Produção' : 'Desenvolvimento' }}</b></li>
                    <li>Data Atual: <b>{{ now()->format('d/m/Y H:i') }}</b></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
