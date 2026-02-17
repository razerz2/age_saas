<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', 'Agendamento | ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))</title>
    <link rel="icon" href="{{ asset('tailadmin/src/favicon.ico') }}">
    <link href="{{ asset('tailadmin/assets/css/style.css') }}" rel="stylesheet">
    @vite([
        'resources/css/tenant/app.css',
        'resources/js/tenant/app.js',
    ])
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <style>
        /* Mesma correção do layout principal (ver `layouts.tailadmin.app`). */
        @media (prefers-color-scheme: dark) {
            body:not(.dark) .btn-patient-primary {
                background-color: #2563eb;
                border-color: #2563eb;
            }
            body:not(.dark) .btn-patient-primary:hover {
                background-color: #1d4ed8;
                border-color: #1d4ed8;
            }

            body:not(.dark) .btn-patient-secondary {
                background-color: transparent;
                color: #374151;
            }
            body:not(.dark) .btn-patient-secondary:hover {
                background-color: #f9fafb;
                border-color: #9ca3af;
            }

            body:not(.dark) .btn-patient-secondary.text-red-600 { color: #dc2626; }
            body:not(.dark) .btn-patient-secondary.text-gray-500 { color: #6b7280; }
        }

        body.dark .btn-patient-secondary.text-red-600 { color: #f87171; }
        body.dark .btn-patient-secondary.hover\:text-red-800:hover,
        body.dark .btn-patient-secondary.hover\:text-red-900:hover { color: #fecaca; }
    </style>
</head>
<body class="bg-gray-50" data-page="@yield('page', 'public')">
    <!-- ===== Preloader Start ===== -->
    <div
        x-data="{ loaded: true }"
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
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
