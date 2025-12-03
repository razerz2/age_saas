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
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#informacoes" role="tab">Informações</a></li>
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

            {{-- Aba Informações --}}
            <div class="tab-pane fade" id="informacoes" role="tabpanel">
                <h5 class="mb-4">📚 Comandos do Projeto</h5>
                <p class="text-muted mb-4">Documentação dos comandos Artisan disponíveis no sistema.</p>

                {{-- Comandos de Tenant --}}
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-building me-2"></i> Comandos de Tenant</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:diagnose {subdomain}</code>
                            <p class="text-muted mb-0">Diagnostica problemas de login para um tenant específico. Verifica conexão com banco, existência de tabelas, usuários cadastrados e credenciais esperadas.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:test-login {subdomain} {email} {password}</code>
                            <p class="text-muted mb-0">Testa o login de um usuário em um tenant específico. Verifica se as credenciais estão corretas e se a autenticação funciona.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:reset-admin-password {subdomain} [--password=] [--email=]</code>
                            <p class="text-muted mb-0">Redefine a senha do usuário admin de um tenant. Se não informar a senha, uma senha segura será gerada automaticamente.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:fix-password {subdomain} {email} {password}</code>
                            <p class="text-muted mb-0">Corrige ou redefine a senha de um usuário específico do tenant.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:add-module {tenant} {user_id} {module}</code>
                            <p class="text-muted mb-0">Adiciona um módulo de acesso a um usuário do tenant. Exemplo: <code>tenant:add-module exemplo 123 calendars</code></p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:migrate [--tenant=] [--all] [--path=] [--pretend]</code>
                            <p class="text-muted mb-0">Executa migrations pendentes nos bancos dos tenants. Opções: <code>--tenant=ID ou subdomain</code> para um tenant específico, <code>--all</code> para todos (padrão se nenhuma opção for fornecida), <code>--path=</code> para caminho customizado das migrations, <code>--pretend</code> para simular sem executar. Exibe estatísticas de sucesso/falha.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:seed-specialties {tenant?} [--force] [--list]</code>
                            <p class="text-muted mb-0">Executa o seeder de especialidades médicas para uma tenant específica. Use --list para ver todas as tenants disponíveis.</p>
                        </div>
                    </div>
                </div>

                {{-- Comandos de Migrations --}}
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-database me-2"></i> Comandos de Migrations</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenants:migrate-all [--path=] [--pretend]</code>
                            <p class="text-muted mb-0">Executa migrations pendentes em TODAS as tenants existentes. Use --pretend para ver o que seria executado sem executar.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenant:migrate [--tenant=] [--all] [--path=] [--pretend]</code>
                            <p class="text-muted mb-0">Executa migrations pendentes nos bancos dos tenants. Aceita ID ou subdomain do tenant. Use <code>--all</code> para todos (padrão se nenhuma opção for fornecida), <code>--path=</code> para caminho customizado das migrations, <code>--pretend</code> para simular sem executar. Exibe barra de progresso e estatísticas detalhadas.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan app:run-apple-calendar-migrations</code>
                            <p class="text-muted mb-0">Executa migrations relacionadas ao Apple Calendar.</p>
                        </div>
                    </div>
                </div>

                {{-- Comandos de Assinaturas e Faturas --}}
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i> Comandos de Assinaturas e Faturas</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan subscriptions:subscriptions-process</code>
                            <p class="text-muted mb-0">Gera faturas automáticas de assinaturas vencidas e renova os períodos. Processa assinaturas com auto_renew ativo e cria cobranças no Asaas (PIX ou assinatura automática para cartão).</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan invoices:invoices-check-overdue</code>
                            <p class="text-muted mb-0">Verifica faturas vencidas há mais de 5 dias e suspende automaticamente os tenants em atraso.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan invoices:invoices-clear [--force]</code>
                            <p class="text-muted mb-0">Apaga todas as faturas do Asaas e do banco local (modo testes). Use --force para não pedir confirmação.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan invoices:clear-asaas-invoices [--force]</code>
                            <p class="text-muted mb-0">Apaga TODAS as faturas diretamente no Asaas (modo manutenção/testes). Use --force para não pedir confirmação.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan tenants:clear-asaas [--force]</code>
                            <p class="text-muted mb-0">Apaga todos os clientes (tenants) no Asaas e suas faturas locais (modo testes). Use --force para não pedir confirmação.</p>
                        </div>
                    </div>
                </div>

                {{-- Comandos de Agendamentos --}}
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Comandos de Agendamentos</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan recurring-appointments:process</code>
                            <p class="text-muted mb-0">Processa agendamentos recorrentes e gera sessões automaticamente. Verifica regras de recorrência e cria novos appointments quando necessário.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan google-calendar:renew-recurring-events</code>
                            <p class="text-muted mb-0">Renova eventos recorrentes no Google Calendar que estão próximos do fim (para recorrências sem data fim).</p>
                        </div>
                    </div>
                </div>

                {{-- Comandos de Módulos e Acesso --}}
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-key me-2"></i> Comandos de Módulos e Acesso</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan platform:ensure-plans-access</code>
                            <p class="text-muted mb-0">Garante que todos os usuários da Platform tenham acesso ao módulo de planos.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan user:add-module {email=admin@plataforma.com}</code>
                            <p class="text-muted mb-0">Adiciona o módulo notification_templates a um usuário da Platform. Exemplo: <code>user:add-module admin@plataforma.com</code></p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan pre-tenants:add-module-to-users</code>
                            <p class="text-muted mb-0">Adiciona o módulo pre_tenants a todos os usuários da Platform.</p>
                        </div>
                    </div>
                </div>

                {{-- Comandos de Integrações --}}
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-plug me-2"></i> Comandos de Integrações</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <code class="d-block mb-2">php artisan asaas:generate-token</code>
                            <p class="text-muted mb-0">Gera uma nova chave de autenticação para o webhook Asaas e atualiza o arquivo .env. O token gerado deve ser copiado e atualizado no painel do Asaas.</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Dica</h6>
                    <p class="mb-0">Todos os comandos podem ser executados via terminal. Para mais informações sobre um comando específico, use <code>php artisan {comando} --help</code></p>
                </div>
            </div>
        </div>
    </div>
@endsection
