@php
    // Para o paciente, podemos buscar notificações específicas dele
    // Por enquanto, deixamos vazio - pode ser implementado depois
    $unreadCount = 0;
@endphp

<li class="nav-item dropdown">
    <a class="nav-link count-indicator dropdown-toggle position-relative" 
       id="notificationDropdown" 
       href="#" 
       data-bs-toggle="dropdown"
       aria-haspopup="true" 
       aria-expanded="false">
        <i class="mdi mdi-bell-outline"></i>
        @if($unreadCount > 0)
            <span class="count-symbol bg-danger notification-badge">
                <span class="notification-count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            </span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end navbar-dropdown notification-dropdown" 
         aria-labelledby="notificationDropdown"
         style="width: 400px; max-width: 90vw; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border: none; padding: 0; overflow: hidden;">
        
        {{-- Cabeçalho --}}
        <div class="notification-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size: 16px; letter-spacing: 0.5px;">
                        <i class="mdi mdi-bell-ring-outline me-2"></i>Notificações
                    </h6>
                </div>
            </div>
        </div>

        {{-- Lista de Notificações --}}
        <div class="notification-list" style="max-height: 450px; overflow-y: auto;">
            <div class="notification-empty text-center py-5" style="color: #999;">
                <i class="mdi mdi-bell-off-outline" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
                <p class="mb-0" style="font-size: 14px;">Nenhuma notificação encontrada</p>
                <small style="font-size: 12px; opacity: 0.7;">Você está em dia!</small>
            </div>
        </div>

        {{-- Rodapé --}}
        <div class="notification-footer text-center" 
             style="padding: 16px; background: #f8f9fa; border-top: 1px solid #e9ecef;">
            <a href="{{ route('patient.notifications.index') }}" 
               class="text-decoration-none fw-semibold"
               style="color: #667eea; font-size: 13px; transition: color 0.2s;">
                <i class="mdi mdi-eye-outline me-1"></i>
                Ver todas as notificações
                <i class="mdi mdi-chevron-right ms-1" style="font-size: 16px;"></i>
            </a>
        </div>
    </div>
</li>

