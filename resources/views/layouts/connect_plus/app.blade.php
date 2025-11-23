<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — Sistema</title>

    {{-- CSS Principal --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('connect_plus/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

</head>

<body>
    <div class="container-scroller">

        {{-- NAVBAR --}}
        @include('layouts.connect_plus.navbar')

        <div class="container-fluid page-body-wrapper">

            {{-- MENU LATERAL --}}
            @include('layouts.connect_plus.navigation')

            <div class="main-panel">
                <div class="content-wrapper">

                    {{-- CONTEÚDO DAS PÁGINAS --}}
                    @yield('content')

                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="{{ asset('connect_plus/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/vendors/jquery-circle-progress/js/circle-progress.min.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/jquery.cookie.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/misc.js') }}"></script>
    <script src="{{ asset('connect_plus/assets/js/dashboard.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

    @stack('scripts')

</body>

</html>
