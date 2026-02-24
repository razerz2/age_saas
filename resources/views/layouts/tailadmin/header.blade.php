@php
    $headerLogoLightPath = tenant_setting('appearance.logo_light', tenant_setting('appearance.logo', ''));
    $headerLogoDarkPath = tenant_setting('appearance.logo_dark', '');
    $headerLogoVersion = function (?string $path): string {
        if (empty($path)) {
            return '';
        }
        try {
            return '?v=' . Storage::disk('public')->lastModified($path);
        } catch (\Throwable $e) {
            return '';
        }
    };
    $headerLogoLightUrl = $headerLogoLightPath
        ? Storage::url($headerLogoLightPath) . $headerLogoVersion($headerLogoLightPath)
        : asset('tailadmin/assets/images/logo/logo.svg');
    $headerLogoDarkUrl = $headerLogoDarkPath
        ? Storage::url($headerLogoDarkPath) . $headerLogoVersion($headerLogoDarkPath)
        : $headerLogoLightUrl;
@endphp

<!-- Header -->
<header x-data="{menuToggle: false}" class="sticky top-0 z-99999 flex w-full border-gray-200 bg-white xl:border-b dark:border-gray-800 dark:bg-gray-900">
    <div class="flex grow flex-col items-center justify-between xl:flex-row xl:px-6">
        <div class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 lg:py-4 xl:justify-normal xl:border-b-0 xl:px-0 dark:border-gray-800">
            <button :class="sidebarToggle ? 'xl:bg-transparent dark:xl:bg-transparent bg-gray-100 dark:bg-gray-800' : ''" class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg border-gray-200 text-gray-500 xl:h-11 xl:w-11 xl:border dark:border-gray-800 dark:text-gray-400" @click.stop="sidebarToggle = !sidebarToggle">
                <svg class="hidden fill-current xl:block" width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor"/>
                </svg>
            </button>
            <a href="{{ workspace_route('tenant.dashboard') }}" class="xl:hidden">
                <img class="h-10 max-h-10 w-auto object-contain dark:hidden" src="{{ $headerLogoLightUrl }}" alt="Logo" />
                <img class="h-10 max-h-10 w-auto object-contain hidden dark:block" src="{{ $headerLogoDarkUrl }}" alt="Logo" />
            </a>
        </div>

        <div :class="menuToggle ? 'flex' : 'hidden'" class="shadow-theme-md w-full items-center justify-between gap-4 px-5 py-4 xl:flex xl:justify-end xl:px-0 xl:shadow-none">
            <div class="2xsm:gap-3 flex items-center gap-2">
                <!-- Dark Mode -->
                <button class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white" @click.prevent="darkMode = !darkMode">
                    <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM5.96356 4.90357C5.67067 5.19647 5.1958 5.19647 4.9029 4.90357C4.61001 4.61068 4.61001 4.13581 4.9029 3.84292L5.78678 2.95904C6.07968 2.66615 6.55455 2.66615 6.84744 2.95904C7.14034 3.25194 7.14034 3.72681 6.84744 4.0197L5.96356 4.90357Z" fill="currentColor"/>
                    </svg>
                    <svg class="dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z" fill="currentColor"/>
                    </svg>
                </button>

                <!-- Notifications -->
                @include('layouts.tailadmin.notifications')
            </ul>

            @php
                // Lógica de permissão reaproveitada do Connect Plus
                $user = auth('tenant')->user();
                $userName = $user->name ?? 'Usuário';
                $userEmail = $user->email ?? null;
                $userAvatar = $user ? $user->avatar_url : asset('tailadmin/assets/images/user/user-01.jpg');
                
                // Verificar acesso ao módulo de configurações
                $hasSettingsAccess = false;
                if ($user) {
                    if ($user->role === 'admin') {
                        $hasSettingsAccess = true;
                    } else {
                        // Garantir que modules seja sempre um array
                        $userModules = [];
                        if ($user->modules) {
                            if (is_array($user->modules)) {
                                $userModules = $user->modules;
                            } elseif (is_string($user->modules)) {
                                $decoded = json_decode($user->modules, true);
                                $userModules = is_array($decoded) ? $decoded : [];
                            }
                        }
                        $hasSettingsAccess = in_array('settings', $userModules);
                    }
                }
            @endphp

            <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                <a class="flex items-center gap-3 text-gray-700 dark:text-gray-400" href="#" @click.prevent="dropdownOpen = !dropdownOpen">
                    <span class="h-10 w-10 overflow-hidden rounded-full">
                        <img src="{{ $userAvatar ?? asset('tailadmin/assets/images/user/user-01.jpg') }}" alt="User" class="h-10 w-10 rounded-full object-cover">
                    </span>
                    <span class="hidden text-sm font-medium xl:block">{{ $userName ?? 'Usuário' }}</span>
                    <svg :class="dropdownOpen && 'rotate-180'" class="text-gray-500 dark:text-gray-400 stroke-current" width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </a>

                <div x-show="dropdownOpen" class="shadow-theme-lg dark:bg-gray-900 absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ $userName ?? 'Usuário' }}</span>
                        <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $userEmail ?? 'usuario@exemplo.com' }}</span>
                    </div>
                    <ul class="mt-4 flex flex-col gap-1 border-t border-gray-200 pt-3 dark:border-gray-800">
                        <li>
                            <a href="{{ route('tenant.profile.edit', ['slug' => tenant()->subdomain]) }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                                <svg class="text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5Z" fill="currentColor"/>
                                </svg>
                                Meu perfil
                            </a>
                        </li>
                        @if($hasSettingsAccess)
                        <li>
                            <a href="{{ route('tenant.settings.index', ['slug' => tenant()->subdomain]) }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                                <svg class="text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.4858 3.5L13.5182 3.5C13.9233 3.5 14.2518 3.82851 14.2518 4.23377C14.2518 5.9529 16.1129 7.02795 17.602 6.1682C17.9528 5.96567 18.4014 6.08586 18.6039 6.43667L20.1203 9.0631C20.3229 9.41407 20.2027 9.86286 19.8517 10.0655C18.3625 10.9253 18.3625 13.0747 19.8517 13.9345C20.2026 14.1372 20.3229 14.5859 20.1203 14.9369L18.6039 17.5634C18.4013 17.9142 17.9528 18.0344 17.602 17.8318C16.1129 16.9721 14.2518 18.0471 14.2518 19.7663C14.2518 20.1715 13.9233 20.5 13.5182 20.5H10.4858C10.0804 20.5 9.75182 20.1714 9.75182 19.766C9.75182 18.0461 7.88983 16.9717 6.40067 17.8314C6.04945 18.0342 5.60037 17.9139 5.39767 17.5628L3.88167 14.937C3.67903 14.586 3.79928 14.1372 4.15026 13.9346C5.63949 13.0748 5.63946 10.9253 4.15025 10.0655C3.79926 9.86282 3.67901 9.41401 3.88165 9.06303L5.39764 6.43725C5.60034 6.08617 6.04943 5.96581 6.40065 6.16858C7.88982 7.02836 9.75182 5.9539 9.75182 4.23399C9.75182 3.82862 10.0804 3.5 10.4858 3.5Z" fill="currentColor"/>
                                </svg>
                                Configurações
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('tenant.logout', ['slug' => tenant()->subdomain]) }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                                <svg class="text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 14.5C11.4477 14.5 11 13.9477 11 13.4477C11 12.9477 11 12.4477 11 11.9477 11.4477 12.4477 12.4477 13.4477C11.4477 14.5 12 14.5Z" fill="currentColor"/>
                                </svg>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('tenant.logout', ['slug' => tenant()->subdomain]) }}" method="POST" class="d-none">
    @csrf
</form>
