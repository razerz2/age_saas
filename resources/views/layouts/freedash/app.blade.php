<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    @php
        $platformFavicon = sysconfig('platform.favicon');
        $faviconUrl = $platformFavicon ? asset('storage/' . $platformFavicon) : asset('freedash/assets/images/favicon.png');
    @endphp
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconUrl }}">
    <title>AgeClin - Sistema de Agendamento para Clínicas </title>
    <!-- Custom CSS -->
    <link href="{{ asset('freedash/assets/extra-libs/c3/c3.min.css') }}" rel="stylesheet">
    <link href="{{ asset('freedash/assets/libs/chartist/dist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('freedash/assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('freedash/dist/css/style.min.css') }}" rel="stylesheet">
    <!-- Datatables CSS -->
    <link rel="stylesheet"
        href="{{ asset('freedash/assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('freedash/assets/extra-libs/datatables.net-bs4/css/responsive.dataTables.min.css') }}">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader" style="display: none;">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-lg navbar-light">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-lg-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-brand">
                        <!-- Logo icon -->
                        @php
                            $platformLogo = sysconfig('platform.logo');
                            if ($platformLogo) {
                                // Verifica se o arquivo existe no storage
                                $logoPath = storage_path('app/public/' . $platformLogo);
                                if (file_exists($logoPath)) {
                                    // Adiciona timestamp para evitar cache do navegador
                                    $logoUrl = asset('storage/' . $platformLogo) . '?v=' . filemtime($logoPath);
                                } else {
                                    $logoUrl = asset('freedash/assets/images/freedashDark.svg');
                                }
                            } else {
                                $logoUrl = asset('freedash/assets/images/freedashDark.svg');
                            }
                        @endphp
                        <a href="{{ route('Platform.dashboard') }}">
                            <img src="{{ $logoUrl }}" alt="Logo" class="img-fluid">
                        </a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Toggle which is visible on mobile only -->
                    <!-- ============================================================== -->
                    <a class="topbartoggler d-block d-lg-none waves-effect waves-light" href="javascript:void(0)"
                        data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                            class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <!-- Notification -->

                        @include('layouts.freedash.notification')

                        <!-- End Notification -->

                        <!-- ============================================================== -->
                        <!-- User profile -->
                        <!-- ============================================================== -->

                        @include('layouts.freedash.profile')

                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar ps-container ps-theme-default ps-active-y" data-sidebarbg="skin6"
                data-ps-id="ef6ac81f-696e-0b61-021d-834fda65823c">
                <!-- aqui nossa dashboard -->
                @include('layouts.freedash.navigation')

                <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: -339px;">
                    <div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                </div>
                <div class="ps-scrollbar-y-rail" style="top: 339px; height: 844px; right: 3px;">
                    <div class="ps-scrollbar-y" tabindex="0" style="top: 242px; height: 602px;"></div>
                </div>
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper" style="display: block;">
            @yield('content')
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    
    <!-- apps -->
    <script src="{{ asset('freedash/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <!-- apps -->

    <!--Custom JavaScript -->
    <script src="{{ asset('freedash/dist/js/custom.min.js') }}"></script>

    <script src="{{ asset('freedash/dist/js/app-style-switcher.js') }}"></script>
    <script src="{{ asset('freedash/dist/js/feather.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/dist/js/sidebarmenu.js') }}"></script>

    <!--This page JavaScript -->
    <script src="{{ asset('freedash/assets/extra-libs/c3/d3.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/extra-libs/c3/c3.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/chartist/dist/chartist.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js') }}">
    </script>
    <script src="{{ asset('freedash/assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('freedash/dist/js/pages/dashboards/dashboard1.min.js') }}"></script>

    <!--DataTables JavaScript -->
    <script src="{{ asset('freedash/assets/extra-libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/extra-libs/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('freedash/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>

    {{-- Helper para dialogs (substitui alert() e confirm()) --}}
    <script src="{{ asset('freedash/assets/js/platform-dialogs.js') }}"></script>

    {{-- Núcleo do DataTables --}}
    <script src="{{ asset('freedash/assets/extra-libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    {{-- Extensão Responsive do DataTables (bs4) --}}
    <script src="{{ asset('freedash/assets/extra-libs/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>

    {{-- (Opcional) Integração js com Bootstrap 4, se existir no seu pacote --}}
    @if (file_exists(public_path('freedash/assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js')))
        <script src="{{ asset('freedash/assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    @endif

    @stack('scripts')

    <div class="jvectormap-tip" style="display: none; left: 951.125px; top: 377.016px;">Australia</div>
</body>

</html>
