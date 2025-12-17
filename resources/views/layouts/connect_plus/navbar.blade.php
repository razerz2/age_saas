@php
    $customLogo = \App\Models\Tenant\TenantSetting::get('appearance.logo');
    $customLogoMini = \App\Models\Tenant\TenantSetting::get('appearance.logo_mini');
    
    // Logo padrão do sistema
    $systemDefaultLogo = sysconfig('system.default_logo');
    $systemDefaultLogoUrl = $systemDefaultLogo ? asset('storage/' . $systemDefaultLogo) : asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
    
    // Logo padrão configurada na plataforma para tenants
    $defaultLogo = sysconfig('tenant.default_logo');
    $defaultLogoUrl = $defaultLogo ? asset('storage/' . $defaultLogo) : $systemDefaultLogoUrl;
    
    // Logo retrátil padrão configurada na plataforma
    $defaultLogoMini = sysconfig('tenant.default_logo_mini');
    $defaultLogoMiniUrl = $defaultLogoMini ? asset('storage/' . $defaultLogoMini) : ($defaultLogo ? asset('storage/' . $defaultLogo) : $systemDefaultLogoUrl);
    
    // Logo do tenant: usa logo própria se existir, senão usa padrão da plataforma, senão usa padrão do sistema
    $logoUrl = $customLogo ? asset('storage/' . $customLogo) : $defaultLogoUrl;
    
    // Logo retrátil: usa logo_mini própria se existir, senão usa logo própria, senão usa padrão da plataforma, senão usa padrão do sistema
    $logoMiniUrl = $customLogoMini ? asset('storage/' . $customLogoMini) : ($customLogo ? asset('storage/' . $customLogo) : $defaultLogoMiniUrl);
@endphp
<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="{{ workspace_route('tenant.dashboard') }}">
            <img src="{{ $logoUrl }}" alt="logo">
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ workspace_route('tenant.dashboard') }}">
            <img src="{{ $logoMiniUrl }}" alt="logo">
        </a>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>

        <ul class="navbar-nav navbar-nav-right">

            {{-- AJUDA / MANUAL --}}
            <li class="nav-item">
                <a class="nav-link" 
                   href="{{ route('landing.manual') }}" 
                   target="_blank"
                   title="Ajuda / Manual do Sistema"
                   data-bs-toggle="tooltip"
                   data-bs-placement="bottom">
                    <i class="mdi mdi-help-circle-outline"></i>
                </a>
            </li>

            {{-- NOTIFICAÇÕES --}}
            @include('layouts.connect_plus.notifications')

            {{-- PERFIL DO USUÁRIO --}}
            @include('layouts.connect_plus.profile')

        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
