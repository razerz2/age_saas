@php
    use App\Models\Tenant\Notification;
    use App\Services\TenantNotificationService;
    use Carbon\Carbon;
    
    // Busca as últimas 10 notificações
    $notifications = Notification::orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    
    $unreadCount = TenantNotificationService::unreadCount();
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
                    @if($unreadCount > 0)
                        <small class="opacity-75" style="font-size: 12px;">
                            {{ $unreadCount }} {{ $unreadCount === 1 ? 'nova' : 'novas' }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lista de Notificações --}}
        <div class="notification-list" style="max-height: 450px; overflow-y: auto;">
            @forelse($notifications as $notification)
                @php
                    $levelColors = [
                        'error' => ['bg' => '#f44336', 'icon' => 'mdi-alert-circle'],
                        'warning' => ['bg' => '#ff9800', 'icon' => 'mdi-alert'],
                        'info' => ['bg' => '#2196f3', 'icon' => 'mdi-information'],
                        'success' => ['bg' => '#4caf50', 'icon' => 'mdi-check-circle']
                    ];
                    $color = $levelColors[$notification->level] ?? $levelColors['info'];
                    $isNew = $notification->status === 'new';
                    
                    // Ícone baseado no tipo
                    $typeIcons = [
                        'appointment' => 'mdi-calendar-check',
                        'form_response' => 'mdi-file-document-edit',
                    ];
                    $icon = $typeIcons[$notification->type] ?? 'mdi-bell';
                @endphp
                
                <a href="{{ workspace_route('tenant.notifications.show', ['notification' => $notification->id]) }}" 
                   class="notification-item d-flex align-items-start text-decoration-none position-relative {{ $isNew ? 'notification-new' : '' }}"
                   data-notification-id="{{ $notification->id }}"
                   style="padding: 16px 20px; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; background: {{ $isNew ? '#f8f9ff' : 'white' }}; text-decoration: none !important; color: inherit;">
                    
                    {{-- Indicador de nova notificação --}}
                    @if($isNew)
                        <div class="notification-indicator" 
                             style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);"></div>
                    @endif
                    
                    {{-- Ícone --}}
                    <div class="notification-icon-wrapper me-3 flex-shrink-0">
                        <div class="notification-icon" 
                             style="width: 48px; height: 48px; border-radius: 12px; background: {{ $color['bg'] }}; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <i class="mdi {{ $icon }} text-white" style="font-size: 22px;"></i>
                        </div>
                    </div>
                    
                    {{-- Conteúdo --}}
                    <div class="notification-content flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="notification-title mb-0 fw-semibold" 
                                style="font-size: 14px; color: #1a1a1a; line-height: 1.4; margin-bottom: 4px;">
                                {{ $notification->title }}
                            </h6>
                            @if($isNew)
                                <span class="badge bg-primary rounded-pill" 
                                      style="font-size: 9px; padding: 2px 6px; margin-left: 8px;">
                                    Nova
                                </span>
                            @endif
                        </div>
                        <p class="notification-message mb-2 text-muted" 
                           style="font-size: 13px; line-height: 1.5; color: #666; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ Str::limit($notification->message, 80) }}
                        </p>
                        <small class="notification-time text-muted d-flex align-items-center" 
                               style="font-size: 11px; color: #999;">
                            <i class="mdi mdi-clock-outline me-1" style="font-size: 12px;"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                </a>
            @empty
                <div class="notification-empty text-center py-5" style="color: #999;">
                    <i class="mdi mdi-bell-off-outline" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
                    <p class="mb-0" style="font-size: 14px;">Nenhuma notificação encontrada</p>
                    <small style="font-size: 12px; opacity: 0.7;">Você está em dia!</small>
                </div>
            @endforelse
        </div>

        {{-- Rodapé --}}
        @if($notifications->count() > 0)
            <div class="notification-footer text-center" 
                 style="padding: 16px; background: #f8f9fa; border-top: 1px solid #e9ecef;">
                <a href="{{ workspace_route('tenant.notifications.index') }}" 
                   class="text-decoration-none fw-semibold"
                   style="color: #667eea; font-size: 13px; transition: color 0.2s;">
                    <i class="mdi mdi-eye-outline me-1"></i>
                    Ver todas as notificações
                    <i class="mdi mdi-chevron-right ms-1" style="font-size: 16px;"></i>
                </a>
            </div>
        @endif
    </div>
</li>

{{-- Estilos CSS --}}
<style>
    .notification-dropdown {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-item:hover {
        background: #f5f7ff !important;
        transform: translateX(2px);
    }

    .notification-item:active {
        transform: translateX(0);
    }

    .notification-list::-webkit-scrollbar {
        width: 6px;
    }

    .notification-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notification-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .notification-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .notification-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        color: white;
        animation: pulse 2s infinite;
    }

    .notification-count {
        line-height: 1;
        font-size: 10px;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }

    .notification-new {
        animation: newNotification 0.5s ease-out;
    }

    @keyframes newNotification {
        0% {
            background: rgba(102, 126, 234, 0.2);
        }
        100% {
            background: #f8f9ff;
        }
    }

    /* Responsividade */
    @media (max-width: 576px) {
        .notification-dropdown {
            width: 100vw !important;
            max-width: 100vw !important;
            margin-left: -10px !important;
            margin-right: -10px !important;
        }
    }
</style>

{{-- Scripts JavaScript --}}
@push('scripts')
<script>
$(document).ready(function() {
    let notificationCheckInterval;
    
    // Função para gerar URL de notificação
    function getNotificationUrl(notificationId) {
        const baseUrl = "{{ workspace_route('tenant.notifications.index') }}";
        return baseUrl + '/' + notificationId;
    }

    // Função para atualizar notificações
    function loadNotifications() {
        $.ajax({
            url: "{{ workspace_route('tenant.notifications.json') }}",
            type: "GET",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                updateNotificationBadge(data.unread_count);
                updateNotificationList(data.notifications);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao buscar notificações:', error);
            }
        });
    }

    // Atualiza o badge de contagem
    function updateNotificationBadge(count) {
        const $badge = $('.notification-badge');
        const $count = $('.notification-count');
        
        if (count > 0) {
            if ($badge.length === 0) {
                // Cria o badge se não existir
                $('#notificationDropdown').append(
                    '<span class="count-symbol bg-danger notification-badge">' +
                    '<span class="notification-count">' + (count > 99 ? '99+' : count) + '</span>' +
                    '</span>'
                );
            } else {
                $count.text(count > 99 ? '99+' : count);
                $badge.show();
            }
        } else {
            $badge.hide();
        }

        // Atualiza o texto do cabeçalho
        const $headerText = $('.notification-header small');
        if (count > 0) {
            const text = count === 1 ? 'nova' : 'novas';
            if ($headerText.length === 0) {
                $('.notification-header h6').after(
                    '<small class="opacity-75" style="font-size: 12px;">' + count + ' ' + text + '</small>'
                );
            } else {
                $headerText.text(count + ' ' + text);
            }
        } else {
            $headerText.remove();
        }
    }

    // Atualiza a lista de notificações
    function updateNotificationList(notifications) {
        const $list = $('.notification-list');
        
        if (notifications.length === 0) {
            $list.html(
                '<div class="notification-empty text-center py-5" style="color: #999;">' +
                '<i class="mdi mdi-bell-off-outline" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>' +
                '<p class="mb-0" style="font-size: 14px;">Nenhuma notificação encontrada</p>' +
                '<small style="font-size: 12px; opacity: 0.7;">Você está em dia!</small>' +
                '</div>'
            );
            $('.notification-footer').hide();
            return;
        }

        $list.empty();
        $('.notification-footer').show();

        const levelColors = {
            'error': { bg: '#f44336', icon: 'mdi-alert-circle' },
            'warning': { bg: '#ff9800', icon: 'mdi-alert' },
            'info': { bg: '#2196f3', icon: 'mdi-information' },
            'success': { bg: '#4caf50', icon: 'mdi-check-circle' }
        };

        const typeIcons = {
            'appointment': 'mdi-calendar-check',
            'form_response': 'mdi-file-document-edit',
        };

        notifications.forEach(function(n) {
            const color = levelColors[n.level] || levelColors['info'];
            const isNew = n.status === 'new';
            const icon = typeIcons[n.type] || 'mdi-bell';
            const createdDate = new Date(n.created_at);
            const timeAgo = getTimeAgo(createdDate);
            const message = n.message.length > 80 ? n.message.substring(0, 80) + '...' : n.message;

            const item = 
                '<a href="' + getNotificationUrl(n.id) + '" ' +
                'class="notification-item d-flex align-items-start text-decoration-none position-relative ' + (isNew ? 'notification-new' : '') + '" ' +
                'data-notification-id="' + n.id + '" ' +
                'style="padding: 16px 20px; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; background: ' + (isNew ? '#f8f9ff' : 'white') + '; text-decoration: none !important; color: inherit;">' +
                (isNew ? '<div class="notification-indicator" style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);"></div>' : '') +
                '<div class="notification-icon-wrapper me-3 flex-shrink-0">' +
                '<div class="notification-icon" style="width: 48px; height: 48px; border-radius: 12px; background: ' + color.bg + '; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">' +
                '<i class="mdi ' + icon + ' text-white" style="font-size: 22px;"></i>' +
                '</div>' +
                '</div>' +
                '<div class="notification-content flex-grow-1" style="min-width: 0;">' +
                '<div class="d-flex justify-content-between align-items-start mb-1">' +
                '<h6 class="notification-title mb-0 fw-semibold" style="font-size: 14px; color: #1a1a1a; line-height: 1.4; margin-bottom: 4px;">' +
                escapeHtml(n.title) +
                '</h6>' +
                (isNew ? '<span class="badge bg-primary rounded-pill" style="font-size: 9px; padding: 2px 6px; margin-left: 8px;">Nova</span>' : '') +
                '</div>' +
                '<p class="notification-message mb-2 text-muted" style="font-size: 13px; line-height: 1.5; color: #666; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">' +
                escapeHtml(message) +
                '</p>' +
                '<small class="notification-time text-muted d-flex align-items-center" style="font-size: 11px; color: #999;">' +
                '<i class="mdi mdi-clock-outline me-1" style="font-size: 12px;"></i>' +
                timeAgo +
                '</small>' +
                '</div>' +
                '</a>';

            $list.append(item);
        });
    }

    // Função para calcular tempo relativo
    function getTimeAgo(date) {
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // diferença em segundos

        if (diff < 60) return 'agora';
        if (diff < 3600) return Math.floor(diff / 60) + ' min atrás';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h atrás';
        if (diff < 604800) return Math.floor(diff / 86400) + ' dias atrás';
        
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    }

    // Função para escapar HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Quando o dropdown é aberto, atualiza as notificações
    $('#notificationDropdown').on('click', function() {
        loadNotifications();
    });

    // Carrega notificações ao inicializar
    loadNotifications();

    // Atualiza a cada 30 segundos
    notificationCheckInterval = setInterval(loadNotifications, 30000);

    // Pausa a atualização quando a página não está visível
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(notificationCheckInterval);
        } else {
            loadNotifications();
            notificationCheckInterval = setInterval(loadNotifications, 30000);
        }
    });
});
</script>
@endpush
