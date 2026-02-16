@extends('layouts.freedash.app')
@section('title', 'Configurações')

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
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#logos" role="tab">Logos e Favicons</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#email" role="tab">E-mail</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pagamentos" role="tab">Pagamentos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#notificacoes" role="tab">Notificações</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#comandos" role="tab">Comandos Agendados</a></li>
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

                    <h5>💬 WhatsApp</h5>
                    <div class="mb-3">
                        <label>Provedor WhatsApp</label>
                        <select class="form-select" name="WHATSAPP_PROVIDER" id="whatsapp-provider-select">
                            <option value="whatsapp_business" {{ $settings['WHATSAPP_PROVIDER'] == 'whatsapp_business' ? 'selected' : '' }}>
                                WhatsApp Business (Meta)
                            </option>
                            <option value="zapi" {{ $settings['WHATSAPP_PROVIDER'] == 'zapi' ? 'selected' : '' }}>
                                Z-API
                            </option>
                            <option value="waha" {{ $settings['WHATSAPP_PROVIDER'] == 'waha' ? 'selected' : '' }}>
                                WAHA
                            </option>
                        </select>
                        <small class="text-muted">Escolha qual provedor de WhatsApp será usado pelo sistema.</small>
                    </div>

                    {{-- Meta / WhatsApp Business --}}
                    <div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="whatsapp_business">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">💬 Meta (WhatsApp Business)</h6>
                            <span id="meta-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                        </div>
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
                        <div class="d-flex flex-column flex-sm-row gap-2 mb-2">
                            <button type="button" class="btn btn-outline-secondary" id="btn-test-meta"
                                    data-test-url="{{ route('Platform.settings.test', 'meta') }}">
                                <i class="fas fa-plug me-1"></i> Testar Conexão Meta
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btn-toggle-meta-send">
                                <i class="fas fa-paper-plane me-1"></i> Testar Envio Meta
                            </button>
                        </div>
                        <small id="meta-test-message" class="text-muted d-block mb-2"></small>

                        <div id="meta-send-form" class="border rounded p-3 bg-light d-none">
                            <div class="mb-2">
                                <label for="meta-test-number" class="form-label">Número de destino</label>
                                <input type="text" id="meta-test-number" class="form-control" placeholder="Ex: 5511999999999">
                            </div>
                            <div class="mb-2">
                                <label for="meta-test-message-input" class="form-label">Mensagem</label>
                                <textarea id="meta-test-message-input" class="form-control" rows="3">Teste de envio Meta - Plataforma AgeClin</textarea>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                                <button type="button" class="btn btn-success" id="btn-send-meta-test"
                                        data-send-url="{{ route('Platform.settings.test.meta.send') }}">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar teste
                                </button>
                                <span id="meta-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                            </div>
                            <small id="meta-send-message" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>

                    {{-- Z-API --}}
                    <div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="zapi">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">📱 Z-API (WhatsApp)</h6>
                            <span id="zapi-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                        </div>
                        <div class="mb-3">
                            <label>API URL</label>
                            <input type="text" class="form-control" name="ZAPI_API_URL"
                                   value="{{ $settings['ZAPI_API_URL'] }}" placeholder="https://api.z-api.io">
                        </div>
                        <div class="mb-3">
                            <label>Token</label>
                            <input type="text" class="form-control" name="ZAPI_TOKEN"
                                   value="{{ $settings['ZAPI_TOKEN'] }}" placeholder="Token da instância">
                            <small class="text-muted">Token da instância Z-API (usado na URL).</small>
                        </div>
                        <div class="mb-3">
                            <label>Client Token</label>
                            <input type="text" class="form-control" name="ZAPI_CLIENT_TOKEN"
                                   value="{{ $settings['ZAPI_CLIENT_TOKEN'] }}" placeholder="Client-Token de segurança">
                            <small class="text-muted">Client-Token de segurança da conta (usado no header).</small>
                        </div>
                        <div class="mb-3">
                            <label>Instance ID</label>
                            <input type="text" class="form-control" name="ZAPI_INSTANCE_ID"
                                   value="{{ $settings['ZAPI_INSTANCE_ID'] }}" placeholder="ID da instância">
                            <small class="text-muted">ID da instância Z-API.</small>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 mb-2">
                            <button type="button" class="btn btn-outline-secondary" id="btn-test-zapi"
                                    data-test-url="{{ route('Platform.settings.test', 'zapi') }}">
                                <i class="fas fa-plug me-1"></i> Testar Conexão Z-API
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btn-toggle-zapi-send">
                                <i class="fas fa-paper-plane me-1"></i> Testar Envio Z-API
                            </button>
                        </div>
                        <small id="zapi-test-message" class="text-muted d-block mb-2"></small>

                        <div id="zapi-send-form" class="border rounded p-3 bg-light d-none">
                            <div class="mb-2">
                                <label for="zapi-test-number" class="form-label">Número de destino</label>
                                <input type="text" id="zapi-test-number" class="form-control" placeholder="Ex: 5511999999999">
                            </div>
                            <div class="mb-2">
                                <label for="zapi-test-message-input" class="form-label">Mensagem</label>
                                <textarea id="zapi-test-message-input" class="form-control" rows="3">Teste de envio Z-API - Plataforma AgeClin</textarea>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                                <button type="button" class="btn btn-success" id="btn-send-zapi-test"
                                        data-send-url="{{ route('Platform.settings.test.zapi.send') }}">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar teste
                                </button>
                                <span id="zapi-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                            </div>
                            <small id="zapi-send-message" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>

                    {{-- WAHA --}}
                    <div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="waha">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">🔄 WAHA (WhatsApp Gateway)</h6>
                            <span id="waha-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
                        </div>
                        <div class="mb-3">
                            <label>Base URL</label>
                            <input type="text" class="form-control" name="WAHA_BASE_URL"
                                   value="{{ $settings['WAHA_BASE_URL'] }}" placeholder="https://seu-servidor-waha">
                        </div>
                        <div class="mb-3">
                            <label>API Key</label>
                            <input type="text" class="form-control" name="WAHA_API_KEY"
                                   value="{{ $settings['WAHA_API_KEY'] }}" placeholder="X-Api-Key">
                        </div>
                        <div class="mb-3">
                            <label>Nome da Sessão</label>
                            <input type="text" class="form-control" name="WAHA_SESSION"
                                   value="{{ $settings['WAHA_SESSION'] }}" placeholder="default">
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 mb-2">
                            <button type="button" class="btn btn-outline-secondary" id="btn-test-waha"
                                    data-test-url="{{ route('Platform.settings.test', 'waha') }}">
                                <i class="fas fa-plug me-1"></i> Testar Sessão WAHA
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btn-toggle-waha-send">
                                <i class="fas fa-paper-plane me-1"></i> Testar Envio WAHA
                            </button>
                        </div>
                        <small id="waha-test-message" class="text-muted d-block mb-2"></small>

                        <div id="waha-send-form" class="border rounded p-3 bg-light d-none">
                            <div class="mb-2">
                                <label for="waha-test-number" class="form-label">Número de destino</label>
                                <input type="text" id="waha-test-number" class="form-control" placeholder="Ex: 5511999999999">
                            </div>
                            <div class="mb-2">
                                <label for="waha-test-message-input" class="form-label">Mensagem</label>
                                <textarea id="waha-test-message-input" class="form-control" rows="3">Teste de envio WAHA - Plataforma AgeClin</textarea>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                                <button type="button" class="btn btn-success" id="btn-send-waha-test"
                                        data-send-url="{{ route('Platform.settings.test.waha.send') }}">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar teste
                                </button>
                                <span id="waha-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
                            </div>
                            <small id="waha-send-message" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Integrações
                        </button>
                    </div>
                </form>
            </div>

            {{-- Aba Logos e Favicons --}}
            <div class="tab-pane fade" id="logos" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.logos') }}" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Logos e Favicons Padrão do Sistema --}}
                    <h5 class="mt-2">⚙️ Logos e Favicons Padrão do Sistema</h5>
                    <p class="text-muted mb-3">Configure as logos e favicons padrão que serão usadas em todo o sistema quando não houver logos personalizadas configuradas.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Logo Padrão do Sistema</label>
                                @php
                                    $defaultLogo = sysconfig('system.default_logo');
                                    if ($defaultLogo) {
                                        $logoPath = storage_path('app/public/' . $defaultLogo);
                                        if (file_exists($logoPath)) {
                                            $defaultLogoUrl = asset('storage/' . $defaultLogo) . '?v=' . filemtime($logoPath);
                                        } else {
                                            $defaultLogoUrl = asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
                                        }
                                    } else {
                                        $defaultLogoUrl = asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
                                    }
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $defaultLogoUrl }}" alt="Logo Padrão Sistema" id="system-default-logo-preview" 
                                         style="max-width: 200px; max-height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="system_default_logo" id="system-default-logo-input" 
                                       accept="image/*" onchange="previewImage(this, 'system-default-logo-preview')">
                                <small class="text-muted">Logo padrão usada em todo o sistema quando não houver logo personalizada</small>
                            </div>
                            @if($defaultLogo)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('system_default_logo')">
                                    <i class="fas fa-trash me-1"></i> Remover Logo
                                </button>
                                <input type="hidden" name="remove_system_default_logo" id="remove-system-default-logo" value="0">
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Favicon Padrão do Sistema</label>
                                @php
                                    $defaultFavicon = sysconfig('system.default_favicon');
                                    if ($defaultFavicon) {
                                        $faviconPath = storage_path('app/public/' . $defaultFavicon);
                                        if (file_exists($faviconPath)) {
                                            $defaultFaviconUrl = asset('storage/' . $defaultFavicon) . '?v=' . filemtime($faviconPath);
                                        } else {
                                            $defaultFaviconUrl = asset('connect_plus/assets/images/favicon.png');
                                        }
                                    } else {
                                        $defaultFaviconUrl = asset('connect_plus/assets/images/favicon.png');
                                    }
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $defaultFaviconUrl }}" alt="Favicon Padrão Sistema" id="system-default-favicon-preview" 
                                         style="max-width: 64px; max-height: 64px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="system_default_favicon" id="system-default-favicon-input" 
                                       accept="image/*" onchange="previewImage(this, 'system-default-favicon-preview')">
                                <small class="text-muted">Recomendado: PNG ou ICO, 16x16 ou 32x32px, máximo 100KB</small>
                            </div>
                            @if($defaultFavicon)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('system_default_favicon')">
                                    <i class="fas fa-trash me-1"></i> Remover Favicon
                                </button>
                                <input type="hidden" name="remove_system_default_favicon" id="remove-system-default-favicon" value="0">
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Logos da Plataforma --}}
                    <h5 class="mt-2">🖼️ Logos da Plataforma</h5>
                    <p class="text-muted mb-3">Configure as logos usadas na área administrativa da plataforma.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Logo da Plataforma</label>
                                @php
                                    $platformLogo = sysconfig('platform.logo');
                                    if ($platformLogo) {
                                        // Verifica se o arquivo existe no storage
                                        $logoPath = storage_path('app/public/' . $platformLogo);
                                        if (file_exists($logoPath)) {
                                            // Adiciona timestamp para evitar cache do navegador
                                            $platformLogoUrl = asset('storage/' . $platformLogo) . '?v=' . filemtime($logoPath);
                                        } else {
                                            $platformLogoUrl = asset('freedash/assets/images/freedashDark.svg');
                                        }
                                    } else {
                                        $platformLogoUrl = asset('freedash/assets/images/freedashDark.svg');
                                    }
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $platformLogoUrl }}" alt="Logo Plataforma" id="platform-logo-preview" 
                                         style="max-width: 200px; max-height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="platform_logo" id="platform-logo-input" 
                                       accept="image/*" onchange="previewImage(this, 'platform-logo-preview')">
                                <small class="text-muted">Recomendado: PNG ou SVG, máximo 500KB</small>
                            </div>
                            @if($platformLogo)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('platform_logo')">
                                    <i class="fas fa-trash me-1"></i> Remover Logo
                                </button>
                                <input type="hidden" name="remove_platform_logo" id="remove-platform-logo" value="0">
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Favicon da Plataforma</label>
                                @php
                                    $platformFavicon = sysconfig('platform.favicon');
                                    $platformFaviconUrl = $platformFavicon ? asset('storage/' . $platformFavicon) : asset('freedash/assets/images/favicon.png');
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $platformFaviconUrl }}" alt="Favicon Plataforma" id="platform-favicon-preview" 
                                         style="max-width: 64px; max-height: 64px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="platform_favicon" id="platform-favicon-input" 
                                       accept="image/*" onchange="previewImage(this, 'platform-favicon-preview')">
                                <small class="text-muted">Recomendado: PNG ou ICO, 16x16 ou 32x32px, máximo 100KB</small>
                            </div>
                            @if($platformFavicon)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('platform_favicon')">
                                    <i class="fas fa-trash me-1"></i> Remover Favicon
                                </button>
                                <input type="hidden" name="remove_platform_favicon" id="remove-platform-favicon" value="0">
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Logo e Favicon da Landing Page --}}
                    <h5 class="mt-4">🌐 Landing Page</h5>
                    <p class="text-muted mb-3">Configure a logo e favicon exibidos na página inicial pública do sistema.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Logo da Landing Page</label>
                                @php
                                    $landingLogo = sysconfig('landing.logo');
                                    $landingLogoUrl = $landingLogo ? asset('storage/' . $landingLogo) : asset('connect_plus/assets/images/logos/landing-page/AllSync-Logo-LP.png');
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $landingLogoUrl }}" alt="Logo Landing Page" id="landing-logo-preview" 
                                         style="max-width: 200px; max-height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="landing_logo" id="landing-logo-input" 
                                       accept="image/*" onchange="previewImage(this, 'landing-logo-preview')">
                                <small class="text-muted">Logo exibida no cabeçalho da landing page</small>
                            </div>
                            @if($landingLogo)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('landing_logo')">
                                    <i class="fas fa-trash me-1"></i> Remover Logo
                                </button>
                                <input type="hidden" name="remove_landing_logo" id="remove-landing-logo" value="0">
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Favicon da Landing Page</label>
                                @php
                                    $landingFavicon = sysconfig('landing.favicon');
                                    $landingFaviconUrl = $landingFavicon ? asset('storage/' . $landingFavicon) : asset('connect_plus/assets/images/favicon.png');
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $landingFaviconUrl }}" alt="Favicon Landing Page" id="landing-favicon-preview" 
                                         style="max-width: 64px; max-height: 64px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="landing_favicon" id="landing-favicon-input" 
                                       accept="image/*" onchange="previewImage(this, 'landing-favicon-preview')">
                                <small class="text-muted">Recomendado: PNG ou ICO, 16x16 ou 32x32px, máximo 100KB</small>
                            </div>
                            @if($landingFavicon)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('landing_favicon')">
                                    <i class="fas fa-trash me-1"></i> Remover Favicon
                                </button>
                                <input type="hidden" name="remove_landing_favicon" id="remove-landing-favicon" value="0">
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Logos Padrão para Tenants --}}
                    <h5 class="mt-4">🏢 Logos Padrão para Tenants</h5>
                    <p class="text-muted mb-3">Configure as logos e favicon padrão que serão usadas quando os tenants não tiverem logos próprias configuradas.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Logo Padrão para Tenants</label>
                                @php
                                    $tenantLogo = sysconfig('tenant.default_logo');
                                    $systemDefaultLogo = sysconfig('system.default_logo');
                                    $systemDefaultLogoUrl = $systemDefaultLogo ? asset('storage/' . $systemDefaultLogo) : asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
                                    if ($tenantLogo) {
                                        $logoPath = storage_path('app/public/' . $tenantLogo);
                                        if (file_exists($logoPath)) {
                                            $tenantLogoUrl = asset('storage/' . $tenantLogo) . '?v=' . filemtime($logoPath);
                                        } else {
                                            $tenantLogoUrl = $systemDefaultLogoUrl;
                                        }
                                    } else {
                                        $tenantLogoUrl = $systemDefaultLogoUrl;
                                    }
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $tenantLogoUrl }}" alt="Logo Tenant Padrão" id="tenant-default-logo-preview" 
                                         style="max-width: 200px; max-height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="tenant_default_logo" id="tenant-default-logo-input" 
                                       accept="image/*" onchange="previewImage(this, 'tenant-default-logo-preview')">
                                <small class="text-muted">Logo usada quando o tenant não tem logo própria</small>
                            </div>
                            @if($tenantLogo)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('tenant_default_logo')">
                                    <i class="fas fa-trash me-1"></i> Remover Logo
                                </button>
                                <input type="hidden" name="remove_tenant_default_logo" id="remove-tenant-default-logo" value="0">
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Logo Retrátil Padrão para Tenants</label>
                                @php
                                    $tenantLogoMini = sysconfig('tenant.default_logo_mini');
                                    $systemDefaultLogo = sysconfig('system.default_logo');
                                    $systemDefaultLogoUrl = $systemDefaultLogo ? asset('storage/' . $systemDefaultLogo) : asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
                                    if ($tenantLogoMini) {
                                        $logoMiniPath = storage_path('app/public/' . $tenantLogoMini);
                                        if (file_exists($logoMiniPath)) {
                                            $tenantLogoMiniUrl = asset('storage/' . $tenantLogoMini) . '?v=' . filemtime($logoMiniPath);
                                        } else {
                                            $tenantLogoMiniUrl = $systemDefaultLogoUrl;
                                        }
                                    } elseif ($tenantLogo) {
                                        $logoPath = storage_path('app/public/' . $tenantLogo);
                                        if (file_exists($logoPath)) {
                                            $tenantLogoMiniUrl = asset('storage/' . $tenantLogo) . '?v=' . filemtime($logoPath);
                                        } else {
                                            $tenantLogoMiniUrl = $systemDefaultLogoUrl;
                                        }
                                    } else {
                                        $tenantLogoMiniUrl = $systemDefaultLogoUrl;
                                    }
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $tenantLogoMiniUrl }}" alt="Logo Retrátil Tenant Padrão" id="tenant-default-logo-mini-preview" 
                                         style="max-width: 80px; max-height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="tenant_default_logo_mini" id="tenant-default-logo-mini-input" 
                                       accept="image/*" onchange="previewImage(this, 'tenant-default-logo-mini-preview')">
                                <small class="text-muted">Logo usada no menu retrátil quando o tenant não tem logo_mini própria</small>
                            </div>
                            @if($tenantLogoMini)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('tenant_default_logo_mini')">
                                    <i class="fas fa-trash me-1"></i> Remover Logo Retrátil
                                </button>
                                <input type="hidden" name="remove_tenant_default_logo_mini" id="remove-tenant-default-logo-mini" value="0">
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Favicon Padrão para Tenants</label>
                                @php
                                    $tenantFavicon = sysconfig('tenant.default_favicon');
                                    $systemDefaultFavicon = sysconfig('system.default_favicon');
                                    $systemDefaultFaviconUrl = $systemDefaultFavicon ? asset('storage/' . $systemDefaultFavicon) : asset('connect_plus/assets/images/favicon.png');
                                    if ($tenantFavicon) {
                                        $faviconPath = storage_path('app/public/' . $tenantFavicon);
                                        if (file_exists($faviconPath)) {
                                            $tenantFaviconUrl = asset('storage/' . $tenantFavicon) . '?v=' . filemtime($faviconPath);
                                        } else {
                                            $tenantFaviconUrl = $systemDefaultFaviconUrl;
                                        }
                                    } else {
                                        $tenantFaviconUrl = $systemDefaultFaviconUrl;
                                    }
                                @endphp
                                <div class="mb-2">
                                    <img src="{{ $tenantFaviconUrl }}" alt="Favicon Tenant Padrão" id="tenant-default-favicon-preview" 
                                         style="max-width: 64px; max-height: 64px; object-fit: contain; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                </div>
                                <input type="file" class="form-control" name="tenant_default_favicon" id="tenant-default-favicon-input" 
                                       accept="image/*" onchange="previewImage(this, 'tenant-default-favicon-preview')">
                                <small class="text-muted">Recomendado: PNG ou ICO, 16x16 ou 32x32px, máximo 100KB</small>
                            </div>
                            @if($tenantFavicon)
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('tenant_default_favicon')">
                                    <i class="fas fa-trash me-1"></i> Remover Favicon
                                </button>
                                <input type="hidden" name="remove_tenant_default_favicon" id="remove-tenant-default-favicon" value="0">
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Logos e Favicons
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

            {{-- Aba Pagamentos --}}
            <div class="tab-pane fade" id="pagamentos" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.billing') }}">
                    @csrf
                    <h5 class="mt-2 mb-4">💳 Configurações de Billing</h5>
                    <p class="text-muted mb-4">Configure os parâmetros automáticos de geração e notificação de faturas.</p>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Geração Automática de Faturas</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="invoice_days_before_due" class="form-label">
                                    <strong>Dias antes do vencimento para gerar faturas</strong>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="invoice_days_before_due" 
                                       name="billing_invoice_days_before_due" 
                                       value="{{ $settings['billing.invoice_days_before_due'] ?? 10 }}" 
                                       min="1" 
                                       max="30">
                                <small class="text-muted">
                                    O comando <code>invoices:generate</code> gerará faturas automaticamente X dias antes do vencimento (apenas para PIX/Boleto). 
                                    Padrão: 10 dias.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-bell me-2"></i> Notificações Preventivas</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="notify_days_before_due" class="form-label">
                                    <strong>Dias antes do vencimento para enviar notificações</strong>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="notify_days_before_due" 
                                       name="billing_notify_days_before_due" 
                                       value="{{ $settings['billing.notify_days_before_due'] ?? 5 }}" 
                                       min="1" 
                                       max="30">
                                <small class="text-muted">
                                    O comando <code>invoices:notify-upcoming</code> enviará notificações Y dias antes do vencimento (exclui faturas de cartão). 
                                    Padrão: 5 dias.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i> Recovery de Assinaturas</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="recovery_days_after_suspension" class="form-label">
                                    <strong>Dias após suspensão para iniciar recovery (cartão)</strong>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="recovery_days_after_suspension" 
                                       name="billing_recovery_days_after_suspension" 
                                       value="{{ $settings['billing.recovery_days_after_suspension'] ?? 5 }}" 
                                       min="1" 
                                       max="30">
                                <small class="text-muted">
                                    O comando <code>subscriptions:process-recovery</code> iniciará o processo de recovery após X dias de suspensão. 
                                    Padrão: 5 dias.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-trash-alt me-2"></i> Purga de Tenants Cancelados</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="purge_days_after_cancellation" class="form-label">
                                    <strong>Dias após cancelamento para purgar dados</strong>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="purge_days_after_cancellation" 
                                       name="billing_purge_days_after_cancellation" 
                                       value="{{ $settings['billing.purge_days_after_cancellation'] ?? 90 }}" 
                                       min="30" 
                                       max="365">
                                <small class="text-muted">
                                    O comando <code>tenants:purge-canceled</code> removerá dados e banco de tenants cancelados há X dias. 
                                    Padrão: 90 dias. <strong>Atenção:</strong> Esta ação é irreversível!
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Informações Importantes</h6>
                        <ul class="mb-0">
                            <li>Essas configurações são específicas para faturas <strong>PIX/Boleto</strong>. Faturas de cartão são gerenciadas exclusivamente pelo Asaas.</li>
                            <li>As configurações são aplicadas imediatamente após salvar.</li>
                            <li>Os comandos agendados (cron jobs) usam essas configurações automaticamente.</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Configurações de Billing
                        </button>
                    </div>
                </form>
            </div>

            {{-- Aba Notificações --}}
            <div class="tab-pane fade" id="notificacoes" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.notifications') }}">
                    @csrf
                    <h5 class="mt-2 mb-4">🔔 Configurações de Notificações</h5>
                    <p class="text-muted mb-4">Configure o comportamento das notificações do sistema na plataforma.</p>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i> Atualização Automática</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notifications_enabled" 
                                           name="notifications_enabled" value="1"
                                           {{ ($settings['notifications.enabled'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifications_enabled">
                                        <strong>Habilitar atualização automática de notificações</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Quando habilitado, as notificações são atualizadas automaticamente sem necessidade de recarregar a página.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="notifications_update_interval" class="form-label">
                                    <strong>Intervalo de atualização (segundos)</strong>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="notifications_update_interval" 
                                       name="notifications_update_interval" 
                                       value="{{ $settings['notifications.update_interval'] ?? 5 }}" 
                                       min="3" 
                                       max="60"
                                       required>
                                <small class="text-muted">
                                    Intervalo em segundos entre cada atualização automática. Mínimo: 3s, Máximo: 60s. Padrão: 5s.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i> Exibição</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="notifications_display_count" class="form-label">
                                    <strong>Quantidade de notificações no dropdown</strong>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="notifications_display_count" 
                                       name="notifications_display_count" 
                                       value="{{ $settings['notifications.display_count'] ?? 5 }}" 
                                       min="3" 
                                       max="20"
                                       required>
                                <small class="text-muted">
                                    Quantidade de notificações exibidas no menu dropdown do sino. Mínimo: 3, Máximo: 20. Padrão: 5.
                                </small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notifications_show_badge" 
                                           name="notifications_show_badge" value="1"
                                           {{ ($settings['notifications.show_badge'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifications_show_badge">
                                        <strong>Exibir badge com contagem de não lidas</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Quando habilitado, exibe um badge com a quantidade de notificações não lidas no ícone do sino.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-bell me-2"></i> Tipos de Eventos</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Selecione quais tipos de eventos devem gerar notificações no sistema.</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_payment" 
                                                   name="notify_payment" value="1"
                                                   {{ ($settings['notifications.types.payment'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_payment">
                                                <strong>💳 Pagamentos</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ms-4">
                                            Notificações sobre pagamentos confirmados, estornados e faturas pagas.
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_invoice" 
                                                   name="notify_invoice" value="1"
                                                   {{ ($settings['notifications.types.invoice'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_invoice">
                                                <strong>📄 Faturas</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ms-4">
                                            Notificações sobre faturas geradas, vencidas, removidas e próximas do vencimento.
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_subscription" 
                                                   name="notify_subscription" value="1"
                                                   {{ ($settings['notifications.types.subscription'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_subscription">
                                                <strong>🔄 Assinaturas</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ms-4">
                                            Notificações sobre criação, atualização, renovação e processamento de assinaturas.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_tenant" 
                                                   name="notify_tenant" value="1"
                                                   {{ ($settings['notifications.types.tenant'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_tenant">
                                                <strong>🏢 Tenants</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ms-4">
                                            Notificações sobre bloqueio, suspensão, purga e alterações de status de tenants.
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_command" 
                                                   name="notify_command" value="1"
                                                   {{ ($settings['notifications.types.command'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_command">
                                                <strong>⚙️ Comandos Executados</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ms-4">
                                            Notificações sobre execução de comandos agendados (processamento de assinaturas, verificação de faturas, etc.).
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="notify_webhook" 
                                                   name="notify_webhook" value="1"
                                                   {{ ($settings['notifications.types.webhook'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_webhook">
                                                <strong>🔗 Webhooks</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ms-4">
                                            Notificações sobre eventos recebidos via webhook (Asaas, etc.). Geralmente apenas para debug.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-volume-up me-2"></i> Notificações Sonoras (Futuro)</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notifications_sound_enabled" 
                                           name="notifications_sound_enabled" value="1" disabled
                                           {{ ($settings['notifications.sound_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifications_sound_enabled" style="opacity: 0.6;">
                                        <strong>Reproduzir som ao receber nova notificação</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Funcionalidade em desenvolvimento. Em breve será possível ativar notificações sonoras.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Informações Importantes</h6>
                        <ul class="mb-0">
                            <li>As configurações são aplicadas imediatamente após salvar.</li>
                            <li>Intervalos muito curtos (menos de 3 segundos) podem sobrecarregar o servidor.</li>
                            <li>Intervalos muito longos (mais de 60 segundos) podem fazer as notificações parecerem desatualizadas.</li>
                            <li>Recomendado: intervalo entre 5 e 10 segundos para melhor equilíbrio entre atualização e performance.</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Configurações de Notificações
                        </button>
                    </div>
                </form>
            </div>

            {{-- Aba Comandos Agendados --}}
            <div class="tab-pane fade" id="comandos" role="tabpanel">
                <form method="POST" action="{{ route('Platform.settings.update.commands') }}">
                    @csrf
                    <h5 class="mt-2 mb-4">⚙️ Comandos Agendados (Cron Jobs)</h5>
                    <p class="text-muted mb-4">Configure os comandos que são executados automaticamente pelo sistema. Os comandos são executados via cron job do Laravel.</p>

                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Informações Importantes</h6>
                        <ul class="mb-0">
                            <li>Os comandos são executados automaticamente pelo agendador do Laravel.</li>
                            <li>Certifique-se de que o cron job está configurado: <code>* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1</code></li>
                            <li>Alterações nos horários são aplicadas imediatamente após salvar.</li>
                            <li>Comandos desabilitados não serão executados, mesmo que o cron esteja rodando.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">Ativo</th>
                                    <th>Comando</th>
                                    <th>Descrição</th>
                                    <th style="width: 150px;">Horário</th>
                                    <th style="width: 100px;">Frequência</th>
                                    <th style="width: 100px;">Dia do Mês</th>
                                    <th style="width: 80px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduledCommands as $command)
                                <tr>
                                    <td class="text-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="command_{{ $command['key'] }}_enabled" 
                                                   name="command_{{ $command['key'] }}_enabled" 
                                                   value="1"
                                                   {{ $command['enabled'] ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-primary">{{ $command['key'] }}</code>
                                        <br>
                                        <strong>{{ $command['name'] }}</strong>
                                        @if(isset($command['is_custom']) && $command['is_custom'])
                                            <br>
                                            <span class="badge bg-success" style="font-size: 10px;">Customizado</span>
                                        @else
                                            <br>
                                            <span class="badge bg-secondary" style="font-size: 10px;">Padrão</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $command['description'] }}</small>
                                    </td>
                                    <td>
                                        <input type="time" 
                                               class="form-control form-control-sm" 
                                               id="command_{{ $command['key'] }}_time" 
                                               name="command_{{ $command['key'] }}_time" 
                                               value="{{ $command['time'] }}"
                                               required>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $command['frequency'] === 'daily' ? 'primary' : 'info' }}">
                                            {{ $command['frequency'] === 'daily' ? 'Diário' : 'Mensal' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($command['frequency'] === 'monthly')
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   id="command_{{ $command['key'] }}_day" 
                                                   name="command_{{ $command['key'] }}_day" 
                                                   value="{{ $command['day'] ?? 1 }}"
                                                   min="1" 
                                                   max="28"
                                                   required>
                                            <small class="text-muted">1-28</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($command['is_custom']) && $command['is_custom'])
                                            <form method="POST" action="{{ route('Platform.settings.commands.remove', $command['key']) }}" 
                                                  onsubmit="return confirm('Tem certeza que deseja remover este comando? Esta ação não pode ser desfeita.');" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Remover comando">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted" title="Comando padrão do sistema não pode ser removido">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Configurações de Comandos
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCommandModal">
                            <i class="fas fa-plus me-1"></i> Adicionar Novo Comando
                        </button>
                        <form method="POST" action="{{ route('Platform.settings.commands.remove-duplicates') }}" 
                              onsubmit="return confirm('Tem certeza que deseja remover todos os comandos duplicados? Esta ação não pode ser desfeita.');" 
                              style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-trash-alt me-1"></i> Remover Duplicados
                            </button>
                        </form>
                        <a href="{{ route('Platform.settings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </form>

                {{-- Modal para adicionar novo comando --}}
                <div class="modal fade" id="addCommandModal" tabindex="-1" aria-labelledby="addCommandModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('Platform.settings.commands.add') }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addCommandModalLabel">
                                        <i class="fas fa-plus-circle me-2"></i>Adicionar Novo Comando Agendado
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <small><i class="fas fa-info-circle me-1"></i> Selecione um comando disponível no sistema ou digite o signature do comando manualmente.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="command_signature" class="form-label">
                                            <strong>Comando (Signature)</strong> <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="command_signature" 
                                                   name="command_signature" 
                                                   placeholder="ex: meu-comando:executar"
                                                   required
                                                   list="availableCommandsList">
                                            <button type="button" class="btn btn-outline-secondary" id="loadCommandsBtn">
                                                <i class="fas fa-sync-alt"></i> Carregar Comandos
                                            </button>
                                        </div>
                                        <datalist id="availableCommandsList"></datalist>
                                        <small class="text-muted">Digite o signature do comando (ex: invoices:generate)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="command_name" class="form-label">
                                            <strong>Nome do Comando</strong> <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="command_name" 
                                               name="command_name" 
                                               placeholder="ex: Meu Comando Personalizado"
                                               required>
                                        <small class="text-muted">Nome amigável para exibição na lista</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="command_description" class="form-label">
                                            <strong>Descrição</strong>
                                        </label>
                                        <textarea class="form-control" 
                                                  id="command_description" 
                                                  name="command_description" 
                                                  rows="2"
                                                  placeholder="Descreva o que este comando faz..."></textarea>
                                        <small class="text-muted">Descrição opcional do comando</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="command_frequency" class="form-label">
                                                    <strong>Frequência</strong> <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select" 
                                                        id="command_frequency" 
                                                        name="command_frequency" 
                                                        required>
                                                    <option value="daily">Diário</option>
                                                    <option value="monthly">Mensal</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="command_time" class="form-label">
                                                    <strong>Horário</strong> <span class="text-danger">*</span>
                                                </label>
                                                <input type="time" 
                                                       class="form-control" 
                                                       id="command_time" 
                                                       name="command_time" 
                                                       value="00:00"
                                                       required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3" id="command_day_container" style="display: none;">
                                        <label for="command_day" class="form-label">
                                            <strong>Dia do Mês</strong>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="command_day" 
                                               name="command_day" 
                                               value="1"
                                               min="1" 
                                               max="28">
                                        <small class="text-muted">Apenas para comandos mensais (1-28)</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Adicionar Comando
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                $(document).ready(function() {
                    // Mostra/oculta campo de dia do mês baseado na frequência
                    $('#command_frequency').on('change', function() {
                        if ($(this).val() === 'monthly') {
                            $('#command_day_container').show();
                            $('#command_day').prop('required', true);
                        } else {
                            $('#command_day_container').hide();
                            $('#command_day').prop('required', false);
                        }
                    });

                    // Carrega lista de comandos disponíveis
                    $('#loadCommandsBtn').on('click', function() {
                        const $btn = $(this);
                        const $datalist = $('#availableCommandsList');
                        
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Carregando...');
                        
                        $.ajax({
                            url: "{{ route('Platform.settings.commands.available') }}",
                            type: "GET",
                            dataType: "json",
                            success: function(data) {
                                $datalist.empty();
                                data.forEach(function(cmd) {
                                    $datalist.append('<option value="' + cmd.signature + '">' + cmd.description + '</option>');
                                });
                                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Carregar Comandos');
                                alert('Comandos carregados! Use o campo de comando para selecionar.');
                            },
                            error: function() {
                                alert('Erro ao carregar comandos. Você pode digitar o signature manualmente.');
                                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Carregar Comandos');
                            }
                        });
                    });

                    // Quando seleciona um comando do datalist, preenche automaticamente
                    $('#command_signature').on('input', function() {
                        const selectedValue = $(this).val();
                        const $option = $('#availableCommandsList option[value="' + selectedValue + '"]');
                        if ($option.length) {
                            // Pode preencher descrição automaticamente se necessário
                        }
                    });
                });
                </script>
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

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage(type) {
            const removeInput = document.getElementById('remove-' + type);
            const fileInput = document.getElementById(type.replace('_', '-') + '-input');
            const previewId = type.replace('_', '-') + '-preview';
            const preview = document.getElementById(previewId);
            
            if (removeInput) {
                removeInput.value = '1';
            }
            
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Reset preview to default
            if (preview) {
                const defaultImages = {
                    'system-default-logo': '{{ asset("connect_plus/assets/images/logos/AllSync-Logo-A.png") }}',
                    'system-default-favicon': '{{ asset("connect_plus/assets/images/favicon.png") }}',
                    'platform-logo': '{{ asset("freedash/assets/images/freedashDark.svg") }}',
                    'platform-favicon': '{{ asset("freedash/assets/images/favicon.png") }}',
                    'landing-logo': '{{ asset("connect_plus/assets/images/logos/landing-page/AllSync-Logo-LP.png") }}',
                    'landing-favicon': '{{ asset("connect_plus/assets/images/favicon.png") }}',
                    'tenant-default-logo': '{{ asset("connect_plus/assets/images/logos/AllSync-Logo-A.png") }}',
                    'tenant-default-logo-mini': '{{ asset("connect_plus/assets/images/logos/AllSync-Logo-A.png") }}',
                    'tenant-default-favicon': '{{ asset("connect_plus/assets/images/favicon.png") }}'
                };
                
                const key = type.replace('_', '-');
                if (defaultImages[key]) {
                    preview.src = defaultImages[key];
                }
            }
        }
    </script>
@endsection

@push('scripts')
    <script>
        (function () {
            function updateWhatsAppProviderVisibility() {
                var select = document.getElementById('whatsapp-provider-select');
                if (!select) return;

                var provider = select.value;
                var sections = document.querySelectorAll('.whatsapp-provider-section');
                sections.forEach(function (section) {
                    var sectionProvider = section.getAttribute('data-provider');
                    if (sectionProvider === provider) {
                        section.style.display = '';
                    } else {
                        section.style.display = 'none';
                    }
                });
            }

            function setupProviderToggle() {
                var select = document.getElementById('whatsapp-provider-select');
                if (!select) return;
                select.addEventListener('change', updateWhatsAppProviderVisibility);
                updateWhatsAppProviderVisibility();
            }

            function setupTestButton(buttonId, badgeId, messageId) {
                var btn = document.getElementById(buttonId);
                if (!btn) return;

                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    var url = btn.getAttribute('data-test-url');
                    if (!url) return;

                    var badge = document.getElementById(badgeId);
                    var message = document.getElementById(messageId);

                    if (badge) {
                        badge.classList.remove('bg-success', 'bg-danger', 'd-none');
                        badge.classList.add('bg-secondary');
                        badge.textContent = 'Testando...';
                    }
                    if (message) {
                        message.textContent = '';
                    }

                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            return response.json().catch(function () {
                                return { status: 'ERROR', message: 'Resposta inválida do servidor.' };
                            });
                        })
                        .then(function (data) {
                            var status = (data && data.status) ? String(data.status).toUpperCase() : 'ERROR';
                            var ok = (status === 'OK' || status === 'WORKING' || status === 'SUCCESS');

                            if (badge) {
                                badge.classList.remove('bg-secondary', 'bg-success', 'bg-danger', 'd-none');
                                badge.classList.add(ok ? 'bg-success' : 'bg-danger');
                                badge.textContent = ok ? 'Conectado' : 'Erro';
                            }

                            if (message) {
                                var friendly = data && data.message ? data.message : (ok
                                    ? 'Conexão realizada com sucesso.'
                                    : 'Falha ao testar conexão. Verifique as configurações.');
                                message.textContent = friendly;
                            }
                        })
                        .catch(function () {
                            if (badge) {
                                badge.classList.remove('bg-secondary', 'bg-success');
                                badge.classList.add('bg-danger');
                                badge.textContent = 'Erro';
                            }
                            if (message) {
                                message.textContent = 'Erro ao comunicar com o servidor. Tente novamente.';
                            }
                        });
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                setupProviderToggle();
                setupTestButton('btn-test-meta', 'meta-test-badge', 'meta-test-message');
                setupTestButton('btn-test-zapi', 'zapi-test-badge', 'zapi-test-message');
                setupTestButton('btn-test-waha', 'waha-test-badge', 'waha-test-message');

                // Toggle formulário de envio Meta
                var toggleMetaSendBtn = document.getElementById('btn-toggle-meta-send');
                var metaSendForm = document.getElementById('meta-send-form');
                if (toggleMetaSendBtn && metaSendForm) {
                    toggleMetaSendBtn.addEventListener('click', function () {
                        if (metaSendForm.classList.contains('d-none')) {
                            metaSendForm.classList.remove('d-none');
                        } else {
                            metaSendForm.classList.add('d-none');
                        }
                    });
                }

                // Toggle formulário de envio Z-API
                var toggleZapiSendBtn = document.getElementById('btn-toggle-zapi-send');
                var zapiSendForm = document.getElementById('zapi-send-form');
                if (toggleZapiSendBtn && zapiSendForm) {
                    toggleZapiSendBtn.addEventListener('click', function () {
                        if (zapiSendForm.classList.contains('d-none')) {
                            zapiSendForm.classList.remove('d-none');
                        } else {
                            zapiSendForm.classList.add('d-none');
                        }
                    });
                }

                function setupSendTest(buttonId, numberId, messageId, badgeId, messageLabelId) {
                    var sendBtn = document.getElementById(buttonId);
                    if (!sendBtn) return;

                    sendBtn.addEventListener('click', function (e) {
                        e.preventDefault();

                        var url = sendBtn.getAttribute('data-send-url');
                        if (!url) return;

                        var numberInput = document.getElementById(numberId);
                        var messageInput = document.getElementById(messageId);
                        var badge = document.getElementById(badgeId);
                        var messageLabel = document.getElementById(messageLabelId);

                        var number = numberInput ? numberInput.value.trim() : '';
                        var text = messageInput ? messageInput.value.trim() : '';

                        if (!number || !text) {
                            if (messageLabel) {
                                messageLabel.textContent = 'Preencha o número de destino e a mensagem para enviar o teste.';
                            }
                            return;
                        }

                        if (badge) {
                            badge.classList.remove('bg-success', 'bg-danger', 'd-none');
                            badge.classList.add('bg-secondary');
                            badge.textContent = 'Enviando...';
                        }
                        if (messageLabel) {
                            messageLabel.textContent = '';
                        }

                        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                number: number,
                                message: text
                            })
                        })
                            .then(function (response) {
                                return response.json();
                            })
                            .then(function (data) {
                                var status = (data && data.status) ? String(data.status).toUpperCase() : 'ERROR';
                                var ok = (status === 'OK' || status === 'WORKING' || status === 'SUCCESS');

                                if (badge) {
                                    badge.classList.remove('bg-secondary', 'bg-success', 'bg-danger', 'd-none');
                                    badge.classList.add(ok ? 'bg-success' : 'bg-danger');
                                    badge.textContent = ok ? 'Enviado' : 'Erro';
                                }

                                if (messageLabel) {
                                    var friendly = data && data.message ? data.message : (ok
                                        ? 'Mensagem enviada com sucesso.'
                                        : 'Falha ao enviar mensagem de teste. Verifique as configurações.');
                                    messageLabel.textContent = friendly;
                                }
                            })
                            .catch(function () {
                                if (badge) {
                                    badge.classList.remove('bg-secondary', 'bg-success');
                                    badge.classList.add('bg-danger');
                                    badge.textContent = 'Erro';
                                }
                                if (messageLabel) {
                                    messageLabel.textContent = 'Erro ao comunicar com o servidor. Tente novamente.';
                                }
                            });
                    });
                }

                setupSendTest('btn-send-meta-test', 'meta-test-number', 'meta-test-message-input', 'meta-send-badge', 'meta-send-message');
                setupSendTest('btn-send-zapi-test', 'zapi-test-number', 'zapi-test-message-input', 'zapi-send-badge', 'zapi-send-message');

                // Toggle formulário de envio WAHA
                var toggleSendBtn = document.getElementById('btn-toggle-waha-send');
                var sendForm = document.getElementById('waha-send-form');
                if (toggleSendBtn && sendForm) {
                    toggleSendBtn.addEventListener('click', function () {
                        if (sendForm.classList.contains('d-none')) {
                            sendForm.classList.remove('d-none');
                        } else {
                            sendForm.classList.add('d-none');
                        }
                    });
                }

                // Envio de mensagem de teste WAHA
                var sendBtn = document.getElementById('btn-send-waha-test');
                if (sendBtn) {
                    sendBtn.addEventListener('click', function (e) {
                        e.preventDefault();

                        var url = sendBtn.getAttribute('data-send-url');
                        if (!url) return;

                        var numberInput = document.getElementById('waha-test-number');
                        var messageInput = document.getElementById('waha-test-message-input');
                        var badge = document.getElementById('waha-send-badge');
                        var message = document.getElementById('waha-send-message');

                        var number = numberInput ? numberInput.value.trim() : '';
                        var text = messageInput ? messageInput.value.trim() : '';

                        if (!number || !text) {
                            if (message) {
                                message.textContent = 'Preencha o número de destino e a mensagem para enviar o teste.';
                            }
                            return;
                        }

                        if (badge) {
                            badge.classList.remove('bg-success', 'bg-danger', 'd-none');
                            badge.classList.add('bg-secondary');
                            badge.textContent = 'Enviando...';
                        }
                        if (message) {
                            message.textContent = '';
                        }

                        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                number: number,
                                message: text
                            })
                        })
                            .then(function (response) {
                                return response.json().catch(function () {
                                    return { status: 'ERROR', message: 'Resposta inválida do servidor.' };
                                });
                            })
                            .then(function (data) {
                                var status = (data && data.status) ? String(data.status).toUpperCase() : 'ERROR';
                                var ok = (status === 'OK' || status === 'WORKING' || status === 'SUCCESS');

                                if (badge) {
                                    badge.classList.remove('bg-secondary', 'bg-success', 'bg-danger', 'd-none');
                                    badge.classList.add(ok ? 'bg-success' : 'bg-danger');
                                    badge.textContent = ok ? 'Enviado' : 'Erro';
                                }

                                if (message) {
                                    var friendly = data && data.message ? data.message : (ok
                                        ? 'Mensagem de teste enviada com sucesso.'
                                        : 'Falha ao enviar mensagem de teste. Verifique as configurações.');
                                    message.textContent = friendly;
                                }
                            })
                            .catch(function () {
                                if (badge) {
                                    badge.classList.remove('bg-secondary', 'bg-success');
                                    badge.classList.add('bg-danger');
                                    badge.textContent = 'Erro';
                                }
                                if (message) {
                                    message.textContent = 'Erro ao comunicar com o servidor. Tente novamente.';
                                }
                            });
                    });
                }
            });
        })();
    </script>
@endpush
