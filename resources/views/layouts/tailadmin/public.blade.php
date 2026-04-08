<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        :root { color-scheme: light; }
        html, body { color-scheme: light; }
    </style>
    <script>
        (function () {
            var ua = window.navigator.userAgent || '';
            var vendor = window.navigator.vendor || '';
            var platform = window.navigator.platform || '';
            var maxTouchPoints = window.navigator.maxTouchPoints || 0;
            var isIOSDevice = /iP(hone|ad|od)/.test(ua) || (platform === 'MacIntel' && maxTouchPoints > 1);
            var hasSafariVersionToken = /Version\/[\d.]+.*Safari/i.test(ua);
            var isExcludedIOSBrowser = /CriOS|FxiOS|EdgiOS|OPiOS|DuckDuckGo|YaBrowser|GSA|OPT|Vivaldi|Focus/i.test(ua);
            var isSafari = /Apple/i.test(vendor) && hasSafariVersionToken && !isExcludedIOSBrowser;

            if (isIOSDevice && isSafari) {
                document.documentElement.classList.add('ios-safari');
            }
        })();
    </script>
    @php($branding = tenant_branding())
    @php($isPublicBookingFlow = request()->routeIs(
        'public.patient.identify',
        'public.patient.register',
        'public.appointment.create',
        'public.appointment.show',
        'public.appointment.success'
    ))
    <title>@yield('title', 'Agendamento | ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))</title>
    <link rel="icon" href="{{ $branding['favicon_url'] }}">
    <link href="{{ asset('tailadmin/assets/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    @vite([
        'resources/css/tenant/app.css',
        'resources/js/tenant/app.js',
    ])
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body
    class="bg-gray-50"
    data-page="@yield('page', 'public')"
    data-public-flow="{{ $isPublicBookingFlow ? 'booking' : 'generic' }}"
>
    <!-- ===== Preloader Start ===== -->
    <div
        x-data="{ loaded: true }"
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white"
    >
        <div
            class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
        ></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Main Content Start ===== -->
    <main>
        @yield('content')
    </main>
    <!-- ===== Main Content End ===== -->

    @include('layouts.tailadmin.dialogs')

    <script src="{{ asset('tailadmin/assets/js/bundle.js') }}"></script>
    <script>
        // Global dialogs API (TailAdmin)
        (function () {
            function dispatch(eventName, detail) {
                try {
                    window.dispatchEvent(new CustomEvent(eventName, { detail: detail || {} }));
                } catch (e) {}
            }
            window.confirmAction = function (opts) { dispatch('ui-confirm', opts || {}); };
            window.showAlert = function (opts) { dispatch('ui-alert', opts || {}); };
        })();
    </script>
    @stack('scripts')
</body>
</html>
