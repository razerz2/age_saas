<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Sistema</title>

    {{-- CSS Principal --}}
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('connect_plus/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">
    @php
        $customFavicon = \App\Models\Tenant\TenantSetting::get('appearance.favicon');
        // Favicon padrão do sistema
        $systemDefaultFavicon = sysconfig('system.default_favicon');
        $systemDefaultFaviconUrl = $systemDefaultFavicon ? asset('storage/' . $systemDefaultFavicon) : asset('connect_plus/assets/images/favicon.ico');
        // Favicon padrão para tenants
        $tenantDefaultFavicon = sysconfig('tenant.default_favicon');
        $tenantDefaultFaviconUrl = $tenantDefaultFavicon ? asset('storage/' . $tenantDefaultFavicon) : $systemDefaultFaviconUrl;
        // Usa favicon personalizado do tenant, senão usa padrão para tenants, senão usa padrão do sistema
        $faviconUrl = $customFavicon ? asset('storage/' . $customFavicon) : $tenantDefaultFaviconUrl;
    @endphp
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/tenant-sidebar-fixed.css') }}">
    
    @stack('styles')
    
    

</head>

<body class="sidebar-fixed">
    <div class="container-scroller">

        {{-- NAVBAR --}}
        @include('layouts.connect_plus.navbar')

        {{-- MENU LATERAL - Fora do page-body-wrapper para ficar fixo --}}
        @include('layouts.connect_plus.navigation')

        <div class="container-fluid page-body-wrapper">
            <div class="main-panel">
                <div class="content-wrapper">

                    {{-- 🔹 Mensagens de Erro --}}
                    @if (session('error'))
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-alert-circle me-3" style="font-size: 1.5rem;"></i>
                                        <div class="flex-grow-1">
                                            <h5 class="alert-heading mb-1">Acesso Negado</h5>
                                            <div class="mb-0">{!! session('error') !!}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 🔹 Mensagens de Sucesso --}}
                    @if (session('success'))
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-check-circle me-3" style="font-size: 1.5rem;"></i>
                                        <div class="flex-grow-1">
                                            <p class="mb-0">{{ session('success') }}</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 🔹 Mensagens de Informação --}}
                    @if (session('info'))
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-information-outline me-3" style="font-size: 1.5rem;"></i>
                                        <div class="flex-grow-1">
                                            <p class="mb-0">{{ session('info') }}</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                                </div>
                            </div>
                        </div>
                    @endif

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

    {{-- Persistência do estado do menu retrátil --}}
    <script>
        (function($) {
            'use strict';
            
            // Restaura o estado do menu retrátil ao carregar a página
            // Executa antes de outros scripts para garantir que o estado seja aplicado
            $(function() {
                var body = $('body');
                var sidebarState = localStorage.getItem('sidebar-icon-only');
                
                // Aplica o estado salvo
                if (sidebarState === 'true') {
                    body.addClass('sidebar-icon-only');
                    // Fecha dropdowns que possam estar abertos
                    setTimeout(function() {
                        $('.sidebar .collapse.show').removeClass('show');
                        $('.sidebar .nav-item .nav-link[aria-expanded="true"]').attr('aria-expanded', 'false');
                    }, 100);
                } else if (sidebarState === 'false') {
                    body.removeClass('sidebar-icon-only');
                }
                // Se não houver estado salvo, mantém o padrão do tema
            });
            
            // Intercepta o toggle do menu para salvar o estado e controlar dropdowns
            // Executa após o evento original para capturar o estado final
            $(document).on('click', '[data-toggle="minimize"]', function() {
                setTimeout(function() {
                    var body = $('body');
                    var isRetracted = body.hasClass('sidebar-icon-only');
                    localStorage.setItem('sidebar-icon-only', isRetracted ? 'true' : 'false');
                    
                    // Se menu foi retraído, fecha todos os dropdowns abertos
                    if (isRetracted) {
                        $('.sidebar .collapse.show').removeClass('show');
                        $('.sidebar .nav-item .nav-link[aria-expanded="true"]').attr('aria-expanded', 'false');
                    }
                }, 150); // Delay para garantir que a classe foi aplicada pelo código original
            });
        })(jQuery);
    </script>

    {{-- Solução para dropdowns no menu retrátil --}}
    <script>
        (function($) {
            'use strict';
            
            // Remove handlers antigos
            $(document).off('mouseenter mouseleave', '.sidebar .nav-item');
            
            // Cria popover para submenus quando sidebar está retraído
            function createSubmenuPopover($navItem) {
                var $collapse = $navItem.find('.collapse');
                if ($collapse.length === 0) return;
                
                var $submenu = $collapse.find('.sub-menu');
                if ($submenu.length === 0) return;
                
                // Remove popover existente
                $navItem.find('.submenu-popover').remove();
                $('.submenu-popover').filter(function() {
                    return $(this).data('nav-item') === $navItem[0];
                }).remove();
                
                // Cria popover
                var $popover = $('<div class="submenu-popover"></div>');
                $popover.data('nav-item', $navItem[0]);
                $popover.html($submenu.clone());
                $popover.css({
                    'position': 'fixed',
                    'background': '#202039',
                    'border-radius': '8px',
                    'padding': '0.5rem 0',
                    'min-width': '200px',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.3)',
                    'z-index': '9999',
                    'display': 'none'
                });
                
                $('body').append($popover);
                $navItem.data('popover', $popover);
            }
            
            // Posiciona e mostra o popover
            function showSubmenuPopover($navItem, event) {
                var $popover = $navItem.data('popover');
                if (!$popover) {
                    createSubmenuPopover($navItem);
                    $popover = $navItem.data('popover');
                }
                
                if (!$popover) return;
                
                var navItemOffset = $navItem.offset();
                var navItemHeight = $navItem.outerHeight();
                var navItemWidth = $navItem.outerWidth();
                
                $popover.css({
                    'top': navItemOffset.top + 'px',
                    'left': (navItemOffset.left + navItemWidth + 10) + 'px',
                    'display': 'block'
                });
            }
            
            // Esconde o popover
            function hideSubmenuPopover($navItem) {
                var $popover = $navItem.data('popover');
                if ($popover) {
                    $popover.hide();
                }
            }
            
            // Desabilita dropdowns quando menu está retraído
            // Usa delegação de eventos com prioridade alta e captura de evento nativo
            var $document = $(document);
            
            // Remove handler anterior se existir
            $document.off('click.sidebar-disable-dropdown');
            
            // Adiciona handler que intercepta ANTES de qualquer outro handler (incluindo Bootstrap)
            $document.on('click.sidebar-disable-dropdown', '.sidebar .nav-item .nav-link[data-bs-toggle="collapse"]', function(e) {
                var body = $('body');
                if (body.hasClass("sidebar-icon-only")) {
                    // Bloqueia completamente o evento
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    
                    var $link = $(this);
                    var target = $link.attr('href') || $link.attr('data-target') || $link.attr('data-bs-target');
                    
                    // Garante que o collapse não está aberto
                    if (target) {
                        var $target = $(target);
                        $target.removeClass('show');
                        $link.attr('aria-expanded', 'false');
                    }
                    
                    // Remove o atributo data-bs-toggle temporariamente para evitar que o Bootstrap processe
                    // (isso é um fallback adicional)
                    return false;
                }
            });
            
            // Também usa addEventListener nativo com capture=true para interceptar ainda mais cedo
            document.addEventListener('click', function(e) {
                var target = e.target;
                while (target && target !== document.body) {
                    if (target.classList.contains('nav-link') && 
                        target.hasAttribute('data-bs-toggle') && 
                        target.getAttribute('data-bs-toggle') === 'collapse' &&
                        target.closest('.sidebar')) {
                        
                        var body = document.body;
                        if (body.classList.contains('sidebar-icon-only')) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            e.stopPropagation();
                            
                            // Fecha qualquer collapse que possa estar aberto
                            var targetId = target.getAttribute('href') || target.getAttribute('data-target') || target.getAttribute('data-bs-target');
                            if (targetId) {
                                var collapseEl = document.querySelector(targetId);
                                if (collapseEl) {
                                    collapseEl.classList.remove('show');
                                }
                                target.setAttribute('aria-expanded', 'false');
                            }
                            
                            return false;
                        }
                    }
                    target = target.parentElement;
                }
            }, true); // true = capture phase (executa antes de outros handlers)
            
            // Função para atualizar estado quando menu é retraído/expandido
            function updateDropdownState() {
                var body = $('body');
                if (body.hasClass("sidebar-icon-only")) {
                    // Fecha todos os dropdowns abertos quando menu é retraído
                    $('.sidebar .collapse.show').removeClass('show');
                    $('.sidebar .nav-item .nav-link[aria-expanded="true"]').attr('aria-expanded', 'false');
                }
            }
            
            // Monitora mudanças no estado do menu usando MutationObserver
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        updateDropdownState();
                    }
                });
            });
            
            // Observa mudanças na classe do body
            if ($('body').length) {
                observer.observe($('body')[0], {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
            
            // Atualiza estado inicial
            updateDropdownState();
            
            // Handler para mouseenter/mouseleave
            $(document).on('mouseenter mouseleave', '.sidebar .nav-item', function(ev) {
                var $navItem = $(this);
                var body = $('body');
                var sidebarIconOnly = body.hasClass("sidebar-icon-only");
                var sidebarFixed = body.hasClass("sidebar-fixed");
                var hasSubmenu = $navItem.find('.collapse').length > 0;
                
                // Se sidebar está retraído E tem submenu
                if (sidebarIconOnly && hasSubmenu) {
                    if (ev.type === 'mouseenter') {
                        // Esconde outros popovers
                        $('.submenu-popover').hide();
                        showSubmenuPopover($navItem, ev);
                    } else {
                        // Delay para permitir movimento do mouse para o popover
                        setTimeout(function() {
                            var $popover = $navItem.data('popover');
                            if ($popover && !$popover.is(':hover') && !$navItem.is(':hover')) {
                                hideSubmenuPopover($navItem);
                            }
                        }, 150);
                    }
                } else if (!sidebarIconOnly) {
                    // Menu expandido: comportamento normal
                    if (sidebarFixed) {
                        // Sidebar fixo: não expande automaticamente
                        $('.submenu-popover').remove();
                        return;
                    } else {
                        // Sidebar não-fixo: mantém hover-open
                        $('.submenu-popover').remove();
                        if (ev.type === 'mouseenter') {
                            $navItem.addClass('hover-open');
                        } else {
                            $navItem.removeClass('hover-open');
                        }
                    }
                }
            });
            
            // Handler para manter popover visível quando mouse está sobre ele
            $(document).on('mouseenter', '.submenu-popover', function() {
                $(this).show();
            });
            
            $(document).on('mouseleave', '.submenu-popover', function() {
                var $this = $(this);
                setTimeout(function() {
                    if (!$this.is(':hover')) {
                        $this.hide();
                    }
                }, 100);
            });
            
            // Limpa popovers quando sidebar expande e fecha dropdowns que estavam abertos
            $(document).on('click', '.navbar-toggler', function() {
                setTimeout(function() {
                    // Atualiza o estado dos dropdowns
                    updateDropdownState();
                    
                    var body = $('body');
                    if (!body.hasClass("sidebar-icon-only")) {
                        // Menu expandido: remove popovers
                        $('.submenu-popover').remove();
                    }
                }, 300);
            });
            
            // Limpa popovers ao clicar fora
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.sidebar, .submenu-popover').length) {
                    $('.submenu-popover').hide();
                }
            });
        })(jQuery);
    </script>
    
    <style>
        /* Estilos para o popover de submenu */
        .submenu-popover {
            font-family: "nunito-medium", sans-serif;
        }
        
        .submenu-popover .sub-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .submenu-popover .sub-menu .nav-item {
            padding: 0;
        }
        
        .submenu-popover .sub-menu .nav-link {
            display: block;
            padding: 0.625rem 1.5rem;
            color: #8e94a9;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .submenu-popover .sub-menu .nav-link:hover {
            background: rgba(194, 244, 219, 0.1);
            color: #c2f4db;
        }
        
        .submenu-popover .sub-menu .nav-link.active {
            background: rgba(194, 244, 219, 0.15);
            color: #c2f4db;
            font-weight: 600;
        }
        
        /* Ajusta posicionamento do popover no RTL */
        .rtl .submenu-popover {
            left: auto !important;
            right: auto !important;
        }
        
        /* Melhora visibilidade do menu retraído - mantém centralização original */
        .sidebar-icon-only .sidebar .nav .nav-item .nav-link {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            flex-direction: column !important;
        }
        
        .sidebar-icon-only .sidebar .nav .nav-item .nav-link .icon-bg {
            margin-right: auto !important;
            margin-left: auto !important;
            margin-bottom: 0.5rem !important;
        }
        
        .sidebar-icon-only .sidebar .nav .nav-item .nav-link .menu-arrow {
            display: none !important;
        }
        
        /* Estilos para scrollbar do menu - mantém aparência consistente */
        .sidebar .nav::-webkit-scrollbar,
        .sidebar-fixed .nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar .nav::-webkit-scrollbar-track,
        .sidebar-fixed .nav::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }
        
        .sidebar .nav::-webkit-scrollbar-thumb,
        .sidebar-fixed .nav::-webkit-scrollbar-thumb {
            background: rgba(142, 148, 169, 0.3);
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        
        .sidebar .nav::-webkit-scrollbar-thumb:hover,
        .sidebar-fixed .nav::-webkit-scrollbar-thumb:hover {
            background: rgba(142, 148, 169, 0.5);
        }
        
        /* Estilos para PerfectScrollbar (se estiver sendo usado) */
        .sidebar .nav .ps__rail-y,
        .sidebar-fixed .nav .ps__rail-y {
            opacity: 0.4;
            width: 6px !important;
            right: 0 !important;
            background: transparent !important;
            transition: opacity 0.2s ease;
        }
        
        .sidebar .nav .ps__rail-y:hover,
        .sidebar-fixed .nav .ps__rail-y:hover {
            opacity: 0.8;
            width: 6px !important;
        }
        
        .sidebar .nav .ps__thumb-y,
        .sidebar-fixed .nav .ps__thumb-y {
            background-color: rgba(142, 148, 169, 0.4) !important;
            width: 6px !important;
            right: 0 !important;
            border-radius: 3px !important;
            transition: background-color 0.2s ease, width 0.2s ease;
        }
        
        .sidebar .nav .ps__thumb-y:hover,
        .sidebar-fixed .nav .ps__thumb-y:hover {
            background-color: rgba(142, 148, 169, 0.6) !important;
            width: 6px !important;
        }
        
        /* Evita mudança de forma no hover */
        .sidebar .nav .ps__rail-y.ps--clicking .ps__thumb-y,
        .sidebar-fixed .nav .ps__rail-y.ps--clicking .ps__thumb-y {
            width: 6px !important;
        }
        
        .sidebar .nav .ps__rail-y.ps--clicking,
        .sidebar-fixed .nav .ps__rail-y.ps--clicking {
            width: 6px !important;
        }
        
        /* Firefox scrollbar */
        .sidebar .nav,
        .sidebar-fixed .nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(142, 148, 169, 0.3) rgba(0, 0, 0, 0.1);
        }
    </style>

    @stack('scripts')


</body>

</html>
