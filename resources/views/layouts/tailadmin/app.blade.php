<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Dashboard | TailAdmin - Tailwind CSS Admin Dashboard Template')</title>
    <link rel="icon" href="{{ asset('tailadmin/assets/images/logo/logo.svg') }}" type="image/svg+xml">
    <link href="{{ asset('tailadmin/assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('tailadmin/assets/vendor/mdi/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <!-- Grid.js CDN CSS -->
    <link rel="stylesheet" href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css">
    <link href="{{ asset('css/page-headers.css') }}" rel="stylesheet">
    @vite([
        'resources/css/tenant/app.css',
        'resources/js/tenant/app.js',
    ])
    <style>
        /* Botões padrão do Tenant (padrão usado em Users) */
        .btn-patient-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #d1d5db;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
        }

        .btn-patient-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        /* Dark mode do TailAdmin (classe .dark no body) */
        .dark .btn-patient-primary {
            background-color: transparent;
            border-color: #d1d5db;
            color: #ffffff;
        }

        .dark .btn-patient-primary:hover {
            background-color: #1f2937;
            border-color: #9ca3af;
        }
    </style>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.tailwindcss.min.css">
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <style>
        /*
         * Tenant buttons: evita "invisibilidade" no modo claro quando o SO está em dark mode.
         *
         * Muitos templates do tenant ainda têm CSS com:
         *   @media (prefers-color-scheme: dark) { .btn-patient-* { ... } }
         * Isso aplica mesmo quando o TailAdmin está em light (sem classe .dark no body).
         * Resultado: botões podem ficar com texto branco/fundo transparente no modo claro.
         *
         * A regra abaixo garante que, sem `.dark`, prevaleça o estilo claro.
         */
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

            /* Mantém variações explícitas (destrutivo/desabilitado) legíveis no modo claro */
            body:not(.dark) .btn-patient-secondary.text-red-600 { color: #dc2626; }
            body:not(.dark) .btn-patient-secondary.text-gray-500 { color: #6b7280; }
        }

        /* Mantém destrutivos vermelhos também no dark mode (classe .dark do TailAdmin) */
        body.dark .btn-patient-secondary.text-red-600 { color: #f87171; }
        body.dark .btn-patient-secondary.hover\:text-red-800:hover,
        body.dark .btn-patient-secondary.hover\:text-red-900:hover { color: #fecaca; }
    </style>
    <style>
        .table-action-btn {
            padding: 0.5rem !important;
            width: 2.75rem;
            height: 2.75rem;
            min-width: 2.75rem;
            border-radius: 0.75rem;
            gap: 0;
            justify-content: center !important;
            align-items: center !important;
            transition: transform 0.2s ease;
        }

        .table-action-btn svg {
            margin: 0 !important;
            width: 1.25rem;
            height: 1.25rem;
        }

        .table-action-btn:focus-visible {
            outline: 2px solid rgba(59, 130, 246, 0.8);
            outline-offset: 2px;
        }
    </style>
    <style>
        /* DataTables - compatibilidade com dark mode TailAdmin */
        .dark .dataTables_wrapper input,
        .dark .dataTables_wrapper select {
            background-color: #1f2937;
            color: #f9fafb;
            border-color: #4b5563;
        }
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_length label,
        .dark .dataTables_wrapper .dataTables_filter label {
            color: #9ca3af;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #d1d5db !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #374151 !important;
            color: #fff !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: #fff !important;
        }

        .dataTables_wrapper {
            width: 100%;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
        }

        .dataTables_wrapper .dataTables_info {
            margin-top: 0.75rem;
        }
    </style>
</head>
<body
    data-page="@yield('page')"
    x-data="{ 
        page: '{{ request()->segment(1) ?? 'dashboard' }}', 
        'loaded': true, 
        'darkMode': false, 
        'stickyMenu': false, 
        'sidebarToggle': false, 
        'scrollTop': false 
    }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
>
    <!-- ===== Preloader Start ===== -->
    <div
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
        <div
            class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
        ></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        @include('layouts.tailadmin.sidebar')
        
        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            @include('layouts.tailadmin.header')
            
            <!-- ===== Main Content Start ===== -->
            <main>
                <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                    @yield('content')
                </div>
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    @include('layouts.tailadmin.dialogs')

    <script src="{{ asset('tailadmin/assets/js/bundle.js') }}"></script>
    <!-- Grid.js CDN JS -->
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script>
        // Global dialogs API (TailAdmin)
        (function () {
            function dispatch(eventName, detail) {
                try {
                    window.dispatchEvent(new CustomEvent(eventName, { detail: detail || {} }));
                } catch (e) {
                    // IE11 fallback not needed; keep safe.
                }
            }

            window.confirmAction = function (opts) {
                dispatch('ui-confirm', opts || {});
            };

            window.showAlert = function (opts) {
                dispatch('ui-alert', opts || {});
            };
        })();
    </script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.tailwindcss.min.js"></script>

    @stack('scripts')
</body>
</html>
