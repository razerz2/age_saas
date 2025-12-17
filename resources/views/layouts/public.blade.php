<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'AllSync') — {{ config('app.name', 'SaaS') }}</title>
    
    <meta name="description" content="@yield('description', 'Sistema completo de agendamentos para clínicas, psicólogos, odontologias e profissionais de saúde.')">
    
    <!-- Favicon -->
    @php
        $landingFavicon = sysconfig('landing.favicon');
        $systemDefaultFavicon = sysconfig('system.default_favicon');
        $systemDefaultFaviconUrl = $systemDefaultFavicon ? asset('storage/' . $systemDefaultFavicon) : asset('connect_plus/assets/images/favicon.png');
        $landingFaviconUrl = $landingFavicon ? asset('storage/' . $landingFavicon) : $systemDefaultFaviconUrl;
    @endphp
    <link rel="shortcut icon" href="{{ $landingFaviconUrl }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $landingFaviconUrl }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $landingFaviconUrl }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header/Navigation -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    @php
                        $landingLogo = sysconfig('landing.logo');
                        $systemDefaultLogo = sysconfig('system.default_logo');
                        $systemDefaultLogoUrl = $systemDefaultLogo ? asset('storage/' . $systemDefaultLogo) : asset('connect_plus/assets/images/logos/landing-page/AllSync-Logo-LP.png');
                        $landingLogoUrl = $landingLogo ? asset('storage/' . $landingLogo) : $systemDefaultLogoUrl;
                    @endphp
                    <a href="{{ route('landing.home') }}" class="flex items-center">
                        <img src="{{ $landingLogoUrl }}" alt="Logo" class="h-10 object-contain" style="border: none; outline: none; box-shadow: none; background: transparent;">
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex md:space-x-8">
                    <a href="{{ route('landing.home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                        Início
                    </a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="pt-16 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ config('app.name', 'AllSync') }}. Todos os direitos reservados.</p>
                </div>
                <div class="flex space-x-6">
                    <a href="{{ route('public.privacy') }}" class="text-gray-400 hover:text-white transition-colors text-sm">
                        Política de Privacidade
                    </a>
                    <a href="{{ route('public.terms') }}" class="text-gray-400 hover:text-white transition-colors text-sm">
                        Termos de Serviço
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

