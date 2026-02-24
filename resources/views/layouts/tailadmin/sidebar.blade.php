@php
    $user = auth('tenant')->user();
    $userModules = [];
    if ($user && $user->modules) {
        if (is_array($user->modules)) {
            $userModules = $user->modules;
        } elseif (is_string($user->modules)) {
            $decoded = json_decode($user->modules, true);
            $userModules = is_array($decoded) ? $decoded : [];
        }
    }
    $settings = \App\Models\Tenant\TenantSetting::getAll();
    $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
    $hasOnlineAccess = ($user && $user->role === 'admin') || in_array('online_appointments', $userModules);
    $financeEnabled = tenant_setting('finance.enabled') === 'true';
    $hasFinanceAccess = $financeEnabled && (($user && $user->role === 'admin') || in_array('finance', $userModules));
    $hasSettingsAccess = ($user && $user->role === 'admin') || in_array('settings', $userModules);

    $showUnifiedPage = false;
    if ($user) {
        if ($user->role === 'doctor' && $user->doctor) {
            $showUnifiedPage = true;
        } elseif ($user->role === 'user') {
            $allowedDoctorsCount = $user->allowedDoctors()->count();
            if ($allowedDoctorsCount === 1) {
                $showUnifiedPage = true;
            }
        }
    }
    $canCreateCalendar = $user && ($user->is_doctor || !$user->is_doctor);
    
    // Obter labels seguros com fallback garantido
    $professionalLabelPlural = professional_label_plural();
    $professionalLabelSingular = professional_label_singular();
    
    // Validação extra para garantir que nunca sejam vazios ou inválidos
    if (empty(trim($professionalLabelPlural)) || 
        in_array(trim($professionalLabelPlural), ['s', 'ss', ',']) || 
        strlen(trim($professionalLabelPlural)) <= 1) {
        $professionalLabelPlural = 'Profissionais';
    }
    
    if (empty(trim($professionalLabelSingular)) || 
        in_array(trim($professionalLabelSingular), ['s', 'ss', ',']) || 
        strlen(trim($professionalLabelSingular)) <= 1) {
        $professionalLabelSingular = 'Profissional';
    }

    $logoLightPath = tenant_setting('appearance.logo_light', tenant_setting('appearance.logo', ''));
    $logoDarkPath = tenant_setting('appearance.logo_dark', '');
    $logoMiniLightPath = tenant_setting('appearance.logo_mini_light', tenant_setting('appearance.logo_mini', ''));
    $logoMiniDarkPath = tenant_setting('appearance.logo_mini_dark', '');

    $logoVersion = function (?string $path): string {
        if (empty($path)) {
            return '';
        }
        try {
            return '?v=' . Storage::disk('public')->lastModified($path);
        } catch (\Throwable $e) {
            return '';
        }
    };

    $logoLightUrl = $logoLightPath
        ? Storage::url($logoLightPath) . $logoVersion($logoLightPath)
        : asset('tailadmin/assets/images/logo/logo.svg');
    $logoDarkUrl = $logoDarkPath
        ? Storage::url($logoDarkPath) . $logoVersion($logoDarkPath)
        : $logoLightUrl;
    $logoMiniLightUrl = $logoMiniLightPath
        ? Storage::url($logoMiniLightPath) . $logoVersion($logoMiniLightPath)
        : asset('tailadmin/assets/images/logo/logo-icon.svg');
    $logoMiniDarkUrl = $logoMiniDarkPath
        ? Storage::url($logoMiniDarkPath) . $logoVersion($logoMiniDarkPath)
        : $logoMiniLightUrl;
@endphp

<aside
    :class="sidebarToggle ? 'translate-x-0 xl:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[260px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 dark:border-gray-800 dark:bg-black"
    @click.outside="sidebarToggle = false"
>
    <div class="sidebar-header px-4 pt-8 pb-7">
        <div
            :class="sidebarToggle ? 'justify-center' : 'justify-between'"
            class="flex items-center gap-2"
        >
            <a href="{{ workspace_route('tenant.dashboard') }}" class="flex items-center gap-2 min-w-0">
                <span :class="sidebarToggle ? 'hidden' : 'flex'" class="items-center">
                    <img class="block h-10 w-auto object-contain dark:hidden" src="{{ $logoLightUrl }}" alt="Logo" />
                    <img class="hidden h-10 w-auto object-contain dark:block" src="{{ $logoDarkUrl }}" alt="Logo" />
                </span>
                <span :class="sidebarToggle ? 'flex' : 'hidden'" class="items-center">
                    <img class="block h-8 w-auto object-contain dark:hidden" src="{{ $logoMiniLightUrl }}" alt="Logo" />
                    <img class="hidden h-8 w-auto object-contain dark:block" src="{{ $logoMiniDarkUrl }}" alt="Logo" />
                </span>
            </a>
        </div>
    </div>

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <nav>
            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">
                        Menu Principal
                    </span>
                </h3>

                <ul class="mb-6 flex flex-col gap-1">
                    <li>
                        <a
                            href="{{ workspace_route('tenant.dashboard') }}"
                            class="menu-item group {{ request()->routeIs('tenant.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <i class="mdi mdi-view-dashboard {{ request()->routeIs('tenant.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>

                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Dashboard</span>
                        </a>
                    </li>

                    @if ($user && $user->role === 'doctor')
                        <li>
                            <a
                                href="{{ workspace_route('tenant.calendars.events.redirect') }}"
                                class="menu-item group {{ request()->routeIs('tenant.calendars.events.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                            >
                                <i class="mdi mdi-calendar-check {{ request()->routeIs('tenant.calendars.events.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Agenda</span>
                            </a>
                        </li>
                    @endif

                    <li>
                        <a
                            href="{{ workspace_route('tenant.appointments.index') }}"
                            class="menu-item group {{ request()->routeIs('tenant.appointments.*') && !request()->routeIs('tenant.recurring-appointments.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <i class="mdi mdi-calendar-clock {{ request()->routeIs('tenant.appointments.*') && !request()->routeIs('tenant.recurring-appointments.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Agendamentos</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ workspace_route('tenant.recurring-appointments.index') }}"
                            class="menu-item group {{ request()->routeIs('tenant.recurring-appointments.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <i class="mdi mdi-calendar-repeat {{ request()->routeIs('tenant.recurring-appointments.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Agend. Recorrentes</span>
                        </a>
                    </li>

                    @if($hasOnlineAccess && $defaultMode !== 'presencial')
                        <li>
                            <a
                                href="{{ workspace_route('tenant.online-appointments.index') }}"
                                class="menu-item group {{ request()->routeIs('tenant.online-appointments.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                            >
                                <i class="mdi mdi-video-account {{ request()->routeIs('tenant.online-appointments.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Consultas Online</span>
                            </a>
                        </li>
                    @endif

                    @if(has_module('medical_appointments'))
                        <li>
                            <a
                                href="{{ workspace_route('tenant.medical-appointments.index') }}"
                                class="menu-item group {{ request()->routeIs('tenant.medical-appointments.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                            >
                                <i class="mdi mdi-account-heart {{ request()->routeIs('tenant.medical-appointments.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Atendimento</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Cadastros</span>
                </h3>
                <ul class="mb-6 flex flex-col gap-1">
                    <li x-data="{ open: {{ request()->routeIs('tenant.patients.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.patients.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-account-heart {{ request()->routeIs('tenant.patients.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Pacientes</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ workspace_route('tenant.patients.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.patients.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os Pacientes</a>
                                </li>
                                <li>
                                    <a href="{{ workspace_route('tenant.patients.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.patients.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo Paciente</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li x-data="{ open: {{ request()->routeIs('tenant.doctors.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.doctors.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-stethoscope {{ request()->routeIs('tenant.doctors.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">{{ $professionalLabelPlural }}</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ workspace_route('tenant.doctors.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.doctors.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os {{ $professionalLabelPlural }}</a>
                                </li>
                                <li>
                                    <a href="{{ workspace_route('tenant.doctors.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.doctors.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo {{ $professionalLabelSingular }}</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li x-data="{ open: {{ request()->routeIs('tenant.specialties.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.specialties.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-pulse {{ request()->routeIs('tenant.specialties.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Especialidades</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ workspace_route('tenant.specialties.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.specialties.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todas as Especialidades</a>
                                </li>
                                <li>
                                    <a href="{{ workspace_route('tenant.specialties.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.specialties.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Nova Especialidade</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @if ($user && $user->role === 'admin')
                        <li x-data="{ open: {{ request()->routeIs('tenant.users.*') ? 'true' : 'false' }} }">
                            <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <i class="mdi mdi-account-multiple {{ request()->routeIs('tenant.users.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Usuários</span>
                                <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <div class="translate transform overflow-hidden" x-show="open">
                                <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                    <li>
                                        <a href="{{ workspace_route('tenant.users.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.users.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os Usuários</a>
                                    </li>
                                    <li>
                                        <a href="{{ workspace_route('tenant.users.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.users.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo Usuário</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Config. de Calendários</span>
                </h3>
                <ul class="mb-6 flex flex-col gap-1">
                    @if($showUnifiedPage)
                        <li>
                            <a
                                href="{{ workspace_route('tenant.doctor-settings.index') }}"
                                class="menu-item group {{ request()->routeIs('tenant.doctor-settings.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                            >
                                <i class="mdi mdi-calendar-month {{ request()->routeIs('tenant.doctor-settings.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Calendário</span>
                            </a>
                        </li>
                    @else
                        <li x-data="{ open: {{ request()->routeIs('tenant.calendars.*') ? 'true' : 'false' }} }">
                            <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.calendars.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <i class="mdi mdi-calendar-month {{ request()->routeIs('tenant.calendars.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Calendários</span>
                                <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <div class="translate transform overflow-hidden" x-show="open">
                                <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                    <li>
                                        <a href="{{ workspace_route('tenant.calendars.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.calendars.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os Calendários</a>
                                    </li>
                                    @if ($canCreateCalendar)
                                        <li>
                                            <a href="{{ workspace_route('tenant.calendars.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.calendars.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo Calendário</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>

                        <li x-data="{ open: {{ request()->routeIs('tenant.business-hours.*') ? 'true' : 'false' }} }">
                            <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.business-hours.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <i class="mdi mdi-clock-outline {{ request()->routeIs('tenant.business-hours.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Horários</span>
                                <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <div class="translate transform overflow-hidden" x-show="open">
                                <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                    <li>
                                        <a href="{{ workspace_route('tenant.business-hours.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.business-hours.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os Horários</a>
                                    </li>
                                    <li>
                                        <a href="{{ workspace_route('tenant.business-hours.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.business-hours.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo Horário</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li x-data="{ open: {{ request()->routeIs('tenant.appointment-types.*') ? 'true' : 'false' }} }">
                            <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.appointment-types.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <i class="mdi mdi-clipboard-pulse {{ request()->routeIs('tenant.appointment-types.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Tipos</span>
                                <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <div class="translate transform overflow-hidden" x-show="open">
                                <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                    <li>
                                        <a href="{{ workspace_route('tenant.appointment-types.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.appointment-types.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os Tipos</a>
                                    </li>
                                    <li>
                                        <a href="{{ workspace_route('tenant.appointment-types.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.appointment-types.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo Tipo</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Formulários</span>
                </h3>
                <ul class="mb-6 flex flex-col gap-1">
                    <li x-data="{ open: {{ request()->routeIs('tenant.forms.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.forms.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-file-document-edit {{ request()->routeIs('tenant.forms.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Formulários</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ workspace_route('tenant.forms.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.forms.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todos os Formulários</a>
                                </li>
                                <li>
                                    <a href="{{ workspace_route('tenant.forms.create') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.forms.create') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-plus text-[14px] text-gray-500 dark:text-gray-400"></i>Novo Formulário</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li x-data="{ open: {{ request()->routeIs('tenant.responses.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.responses.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-file-document-box-check {{ request()->routeIs('tenant.responses.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Respostas</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ workspace_route('tenant.responses.index') }}" class="menu-dropdown-item group flex items-center gap-2 {{ request()->routeIs('tenant.responses.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}"><i class="mdi mdi-view-list text-[14px] text-gray-500 dark:text-gray-400"></i>Todas as Respostas</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            @if($hasFinanceAccess)
                <div>
                    <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                        <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Financeiro</span>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-1">
                        <li x-data="{ open: {{ request()->routeIs('tenant.finance.*') ? 'true' : 'false' }} }">
                            <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.finance.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <i class="mdi mdi-cash {{ request()->routeIs('tenant.finance.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Financeiro</span>
                                <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <div class="translate transform overflow-hidden" x-show="open">
                                <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                    @if($user && $user->role === 'admin')
                                        <li><a href="{{ workspace_route('tenant.finance.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Dashboard</a></li>
                                        <li><a href="{{ workspace_route('tenant.finance.accounts.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.accounts.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Contas</a></li>
                                        <li><a href="{{ workspace_route('tenant.finance.categories.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.categories.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Categorias</a></li>
                                        <li><a href="{{ workspace_route('tenant.finance.transactions.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.transactions.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Transações</a></li>
                                        <li><a href="{{ workspace_route('tenant.finance.charges.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.charges.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Cobranças</a></li>
                                        <li><a href="{{ workspace_route('tenant.finance.commissions.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.commissions.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Comissões</a></li>
                                        <li><a href="{{ workspace_route('tenant.finance.reports.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.reports.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Relatórios</a></li>
                                    @elseif($user && $user->role === 'doctor')
                                        <li><a href="{{ workspace_route('tenant.finance.commissions.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.finance.commissions.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Comissões</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            @endif

            @if($hasSettingsAccess)
                <div>
                    <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                        <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Sistema</span>
                    </h3>
                    <ul class="mb-6 flex flex-col gap-1">
                        <li>
                            <a
                                href="{{ workspace_route('tenant.settings.index') }}"
                                class="menu-item group {{ request()->routeIs('tenant.settings.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                            >
                                <i class="mdi mdi-settings {{ request()->routeIs('tenant.settings.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                                <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Configurações</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Integrações</span>
                </h3>
                <ul class="mb-6 flex flex-col gap-1">
                    <li x-data="{ open: {{ request()->routeIs('tenant.integrations.*') || request()->routeIs('tenant.integrations.google.*') || request()->routeIs('tenant.integrations.apple.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.integrations.*') || request()->routeIs('tenant.integrations.google.*') || request()->routeIs('tenant.integrations.apple.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-puzzle {{ request()->routeIs('tenant.integrations.*') || request()->routeIs('tenant.integrations.google.*') || request()->routeIs('tenant.integrations.apple.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Integrações</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ workspace_route('tenant.integrations.google.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.integrations.google.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Google Calendar</a>
                                </li>
                                <li>
                                    <a href="{{ workspace_route('tenant.integrations.apple.index') }}" class="menu-dropdown-item group {{ request()->routeIs('tenant.integrations.apple.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">Apple Calendar</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">Relatórios</span>
                </h3>
                <ul class="mb-6 flex flex-col gap-1">
                    <li x-data="{ open: {{ request()->routeIs('tenant.reports.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open" class="menu-item group {{ request()->routeIs('tenant.reports.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i class="mdi mdi-chart-bar {{ request()->routeIs('tenant.reports.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text truncate min-w-0" :class="sidebarToggle ? 'xl:hidden' : ''">Relatórios</span>
                            <svg class="menu-item-arrow" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="translate transform overflow-hidden" x-show="open">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'" class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.appointments') }}">Agendamentos</a></li>
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.patients') }}">Pacientes</a></li>
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.doctors') }}">Médicos</a></li>
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.recurring') }}">Recorrências</a></li>
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.forms') }}">Formulários</a></li>
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.portal') }}">Portal do Paciente</a></li>
                                <li><a class="menu-dropdown-item group" href="{{ workspace_route('tenant.reports.notifications') }}">Notificações</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>
