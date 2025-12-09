@php
    $customLogo = \App\Models\Tenant\TenantSetting::get('appearance.logo');
    $logoUrl = $customLogo ? asset('storage/' . $customLogo) : asset('connect_plus/assets/images/logos/AllSync-Logo-A.png');
@endphp
<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="{{ workspace_route('tenant.dashboard') }}">
            <img src="{{ $logoUrl }}" alt="logo">
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ workspace_route('tenant.dashboard') }}">
            <img src="{{ $logoUrl }}" alt="logo">
        </a>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>

        <ul class="navbar-nav navbar-nav-right">

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
