<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administração') — {{ app('currentNetwork')->name ?? 'Rede de Clínicas' }}</title>

    {{-- CSS Principal --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">

    <style>
        /* Sidebar Improvements */
        .sidebar .nav .nav-item .nav-link {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            padding: 0.8rem 1.5rem !important;
            transition: all 0.3s ease;
        }
        .sidebar .nav .nav-item .nav-link .menu-icon {
            order: -1 !important;
            margin-right: 1rem !important;
            margin-left: 0 !important;
            font-size: 1.2rem !important;
            color: #b66dff !important;
            width: 25px;
            text-align: center;
        }
        .sidebar .nav .nav-item .nav-link .menu-title {
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            color: #3e4b5b !important;
        }
        .sidebar .nav .nav-item.active > .nav-link {
            background: #f8f9fa !important;
            border-radius: 0 25px 25px 0;
            margin-right: 10px;
        }
        .sidebar .nav .nav-item.active > .nav-link .menu-title {
            color: #b66dff !important;
            font-weight: 700 !important;
        }
        .sidebar .nav .nav-item.active > .nav-link:before {
            content: "";
            width: 4px;
            height: 100%;
            background: #b66dff;
            position: absolute;
            left: 0;
            top: 0;
            border-radius: 0 2px 2px 0;
        }
        .sidebar .nav .nav-item:hover .nav-link {
            background: #f2edf3 !important;
            border-radius: 0 25px 25px 0;
            margin-right: 10px;
        }

        /* Navbar Improvements */
        .navbar .navbar-brand-wrapper .navbar-brand {
            font-weight: 800 !important;
            letter-spacing: 1px;
            background: linear-gradient(to right, #b66dff, #6a11cb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .navbar .navbar-menu-wrapper {
            box-shadow: 0 4px 20px 0 rgba(0,0,0,.05);
        }

        /* Content Area */
        .main-panel {
            background: #f4f7fa !important;
        }
        .content-wrapper {
            padding: 2rem !important;
        }
        
        /* Card Improvements */
        .card {
            border: none !important;
            border-radius: 15px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
        }

        /* Table Improvements */
        .table thead th {
            border-top: 0 !important;
            border-bottom: 2px solid #ebedf2 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 0.75rem !important;
            color: #3e4b5b !important;
            padding: 1rem !important;
        }
        .table td {
            padding: 1rem !important;
            vertical-align: middle !important;
            font-size: 0.875rem !important;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        /* Form Improvements */
        .form-control, .form-select {
            border-radius: 8px !important;
            border: 1px solid #ebedf2 !important;
            padding: 0.6rem 1rem !important;
            font-size: 0.875rem !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #b66dff !important;
            box-shadow: 0 0 0 0.2rem rgba(182, 109, 255, 0.1) !important;
        }
        .btn {
            border-radius: 8px !important;
            padding: 0.6rem 1.2rem !important;
            font-weight: 600 !important;
            transition: all 0.2s ease;
        }
        .btn-gradient-primary {
            background: linear-gradient(to right, #da8cff, #9a55ff) !important;
            border: none !important;
            color: white !important;
        }
        .btn-gradient-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(182, 109, 255, 0.3) !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="container-scroller">
        {{-- NAVBAR --}}
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">
                    <i class="mdi mdi-layers-outline me-2"></i> {{ app('currentNetwork')->name ?? 'Rede de Clínicas' }}
                </a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-center flex-grow-1">
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black font-weight-bold">{{ auth()->guard('network')->user()->name ?? 'Usuário' }}</p>
                                <p class="mb-0 text-muted small">Administrador</p>
                            </div>
                        </a>
                        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                            <form action="{{ route('network.logout', ['network' => app('currentNetwork')->slug]) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="mdi mdi-logout me-2 text-primary"></i> Sair
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>

        <div class="container-fluid page-body-wrapper">
            {{-- MENU LATERAL --}}
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <span class="bg-gradient-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="mdi mdi-account"></i>
                                </span>
                                <span class="login-status online"></span>
                                <!--change to offline or busy as needed-->
                            </div>
                            <div class="nav-profile-text d-flex flex-column">
                                <span class="font-weight-bold mb-2">{{ auth()->guard('network')->user()->name ?? 'Usuário' }}</span>
                                <span class="text-secondary text-small">Rede {{ app('currentNetwork')->name }}</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('network.dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">
                            <i class="mdi mdi-view-dashboard menu-icon"></i>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('network.clinics.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('network.clinics.index', ['network' => app('currentNetwork')->slug]) }}">
                            <i class="mdi mdi-hospital-building menu-icon"></i>
                            <span class="menu-title">Clínicas</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('network.doctors.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('network.doctors.index', ['network' => app('currentNetwork')->slug]) }}">
                            <i class="mdi mdi-doctor menu-icon"></i>
                            <span class="menu-title">Médicos</span>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('network.appointments.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('network.appointments.index', ['network' => app('currentNetwork')->slug]) }}">
                            <i class="mdi mdi-calendar-clock menu-icon"></i>
                            <span class="menu-title">Agendamentos</span>
                        </a>
                    </li>
                    @if(auth()->guard('network')->user()->canViewFinance())
                    <li class="nav-item {{ request()->routeIs('network.finance.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('network.finance.index', ['network' => app('currentNetwork')->slug]) }}">
                            <i class="mdi mdi-cash menu-icon"></i>
                            <span class="menu-title">Financeiro</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item {{ request()->routeIs('network.settings.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('network.settings.edit', ['network' => app('currentNetwork')->slug]) }}">
                            <i class="mdi mdi-settings menu-icon"></i>
                            <span class="menu-title">Configurações</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="main-panel">
                <div class="content-wrapper">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>

                <footer class="footer">
                    <div class="container-fluid d-flex justify-content-between">
                        <span class="text-muted d-block text-center text-sm-start d-sm-inline-block">Copyright © {{ date('Y') }} {{ app('currentNetwork')->name ?? 'Rede de Clínicas' }}</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="{{ asset('connect_plus/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/misc.js') }}"></script>

    @stack('scripts')
</body>
</html>
