<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Sistema de Agendamentos para Clínicas e Profissionais de Saúde') — {{ config('app.name', 'SaaS') }}</title>
    
    <meta name="description" content="@yield('description', 'Sistema completo de agendamentos para clínicas, psicólogos, odontologias e profissionais de saúde. Agende consultas presenciais e online, gerencie pacientes, médicos e muito mais.')">
    
    @stack('meta')
    
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
    
    @stack('styles')
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
                    <a href="{{ route('landing.home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('landing.home') ? 'text-blue-600 font-semibold' : '' }}">
                        Início
                    </a>
                    <a href="{{ route('landing.features') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('landing.features') ? 'text-blue-600 font-semibold' : '' }}">
                        Funcionalidades
                    </a>
                    <a href="{{ route('landing.plans') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('landing.plans') ? 'text-blue-600 font-semibold' : '' }}">
                        Planos
                    </a>
                    <a href="{{ route('landing.contact') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('landing.contact') ? 'text-blue-600 font-semibold' : '' }}">
                        Contato
                    </a>
                </div>
                
                <!-- CTA Button -->
                <div class="hidden md:flex md:items-center md:space-x-4">
                    <a href="{{ route('landing.plans', array_filter(['trial' => 1, 'plan_id' => $landingTrialPlan?->id])) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Testar Grátis
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700 hover:text-blue-600" id="mobile-menu-button">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 border-t border-gray-200">
                    <a href="{{ route('landing.home') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Início</a>
                    <a href="{{ route('landing.features') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Funcionalidades</a>
                    <a href="{{ route('landing.plans') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Planos</a>
                    <a href="{{ route('landing.contact') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-base font-medium">Contato</a>
                    <a href="{{ route('landing.plans', array_filter(['trial' => 1, 'plan_id' => $landingTrialPlan?->id])) }}" class="block px-3 py-2 bg-blue-600 text-white rounded-md text-base font-medium text-center">Testar Grátis</a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>
    
    @php
        $landingSocialLinks = [
            'facebook' => trim((string) sysconfig('landing.contact.facebook_url')),
            'instagram' => trim((string) sysconfig('landing.contact.instagram_url')),
            'linkedin' => trim((string) sysconfig('landing.contact.linkedin_url')),
            'whatsapp' => trim((string) sysconfig('landing.contact.whatsapp_url')),
        ];
        $landingSocialLinks = collect($landingSocialLinks)
            ->map(fn ($url) => filter_var($url, FILTER_VALIDATE_URL) ? $url : '')
            ->all();
        $hasLandingSocialLinks = collect($landingSocialLinks)->contains(fn ($url) => $url !== '');
    @endphp

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-bold mb-4">Sistema de Agendamentos</h3>
                    <p class="text-gray-400 mb-4">
                        A solução completa para gestão de agendamentos em clínicas, consultórios e estabelecimentos de saúde. 
                        Gerencie pacientes, médicos, formulários e muito mais em um único sistema.
                    </p>
                    @if($hasLandingSocialLinks)
                        <div class="flex flex-wrap gap-4">
                            @if($landingSocialLinks['facebook'] !== '')
                                <a href="{{ $landingSocialLinks['facebook'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
                                    <span class="sr-only">Facebook</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($landingSocialLinks['instagram'] !== '')
                                <a href="{{ $landingSocialLinks['instagram'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($landingSocialLinks['linkedin'] !== '')
                                <a href="{{ $landingSocialLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
                                    <span class="sr-only">LinkedIn</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($landingSocialLinks['whatsapp'] !== '')
                                <a href="{{ $landingSocialLinks['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
                                    <span class="sr-only">WhatsApp</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.52 3.48A11.94 11.94 0 0012.04 0C5.5 0 .18 5.32.18 11.86c0 2.09.55 4.12 1.6 5.91L0 24l6.4-1.68a11.8 11.8 0 005.64 1.44h.01c6.54 0 11.86-5.32 11.86-11.86 0-3.17-1.23-6.15-3.39-8.42zM12.05 21.74h-.01a9.8 9.8 0 01-4.99-1.36l-.36-.21-3.8 1 1.02-3.7-.24-.38a9.8 9.8 0 01-1.5-5.23c0-5.42 4.41-9.83 9.84-9.83 2.62 0 5.08 1.02 6.93 2.87a9.74 9.74 0 012.88 6.95c0 5.42-4.41 9.84-9.83 9.84zm5.39-7.35c-.29-.14-1.72-.85-1.99-.95-.27-.1-.46-.14-.66.14-.19.29-.76.95-.93 1.15-.17.19-.34.22-.63.07-.29-.14-1.2-.44-2.29-1.4-.84-.75-1.41-1.67-1.58-1.95-.17-.29-.02-.44.13-.58.13-.12.29-.32.44-.48.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.51-.07-.14-.66-1.58-.9-2.17-.24-.57-.49-.49-.66-.5h-.56c-.19 0-.48.07-.73.36-.24.29-.95.93-.95 2.28 0 1.34.98 2.64 1.12 2.82.14.19 1.92 2.93 4.66 4.11.65.28 1.15.44 1.55.56.65.21 1.24.18 1.71.11.52-.08 1.72-.7 1.96-1.38.24-.68.24-1.25.17-1.38-.07-.12-.26-.19-.55-.33z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Links Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('landing.home') }}" class="text-gray-400 hover:text-white transition-colors">Início</a></li>
                        <li><a href="{{ route('landing.features') }}" class="text-gray-400 hover:text-white transition-colors">Funcionalidades</a></li>
                        <li><a href="{{ route('landing.plans') }}" class="text-gray-400 hover:text-white transition-colors">Planos</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="text-gray-400 hover:text-white transition-colors">Contato</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Suporte</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="text-gray-400 hover:text-white transition-colors">Fale Conosco</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'SaaS Saúde') }}. Todos os direitos reservados.</p>
                <div class="mt-2 flex flex-wrap justify-center gap-x-6 gap-y-2">
                    <a href="{{ route('public.privacy') }}" class="text-gray-400 hover:text-white transition-colors underline">Política de Privacidade</a>
                    <a href="{{ route('public.terms') }}" class="text-gray-400 hover:text-white transition-colors underline">Termos de Serviço</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Mobile Menu Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
