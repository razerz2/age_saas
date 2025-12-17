@php
    use App\Models\Platform\SystemNotification;

    // Busca as últimas 5 notificações
    $notifications = $notifications ?? SystemNotification::latest('created_at')->take(5)->get();
    $unreadCount = SystemNotification::where('status', 'new')->count();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle pl-md-3 position-relative" href="javascript:void(0)"
       id="bell" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

        {{-- Ícone do sino --}}
        <span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" class="feather feather-bell svg-icon">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </span>

        {{-- Badge de notificações novas --}}
        @if($unreadCount > 0)
            <span class="badge text-bg-primary notify-no rounded-circle">{{ $unreadCount }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-start mt-2 mailbox animated bounceInDown"
         style="min-width: 320px; transform: translateX(-60%);">
        <ul class="list-style-none mb-0">
            <li>
                <div class="message-center notifications position-relative ps-container ps-theme-default"
                     style="max-height: 360px; overflow-y: auto;">
                    {{-- Loop de notificações --}}
                    @forelse($notifications as $n)
                        <a href="{{ route('Platform.system_notifications.show', $n->id) }}"
                           class="message-item d-flex align-items-center border-bottom px-3 py-2 {{ $n->status === 'new' ? 'bg-light' : '' }}">
                            <span class="btn btn-{{ $n->level === 'error' ? 'danger' : ($n->level === 'warning' ? 'warning' : 'info') }} rounded-circle btn-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     class="feather feather-bell text-white">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                            </span>
                            <div class="w-75 d-inline-block v-middle ps-2">
                                <h6 class="message-title mb-0 mt-1 text-dark">{{ $n->title }}</h6>
                                <span class="font-12 text-muted d-block text-truncate">
                                    {{ $n->message }}
                                </span>
                                <span class="font-12 text-muted d-block">
                                    {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-3 text-center text-muted">Nenhuma notificação encontrada</div>
                    @endforelse
                </div>
            </li>

            {{-- Link para página de todas as notificações --}}
            <li>
                <a class="nav-link pt-3 text-center text-dark border-top"
                   href="{{ route('Platform.system_notifications.index') }}">
                    <strong>Ver todas as notificações</strong>
                    <i class="fa fa-angle-right ms-1"></i>
                </a>
            </li>
        </ul>
    </div>
</li>

@push('scripts')
<script>
$(document).ready(function() {
    let notificationInterval;
    
    // Configurações do sistema (valores padrão se não configurados)
    const notificationsEnabled = {{ (sysconfig('notifications.enabled', '1') === '1') ? 'true' : 'false' }};
    const updateInterval = {{ (int) sysconfig('notifications.update_interval', 5) }} * 1000; // Converter para milissegundos
    const displayCount = {{ (int) sysconfig('notifications.display_count', 5) }};
    const showBadge = {{ (sysconfig('notifications.show_badge', '1') === '1') ? 'true' : 'false' }};

    function loadNotifications() {
        $.ajax({
            url: "{{ route('Platform.system_notifications.json') }}",
            type: "GET",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                // Atualiza badge de contagem
                updateNotificationBadge(data.unread_count);
                
                // Atualiza lista de notificações no dropdown
                updateNotificationList(data.notifications);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao buscar notificações:', error);
                // Não interrompe o intervalo em caso de erro
            }
        });
    }

    function updateNotificationBadge(count) {
        // Verifica se o badge deve ser exibido
        if (!showBadge) {
            $('.notify-no').hide();
            return;
        }
        
        var $bell = $('#bell');
        var $badge = $('.notify-no');
        
        if (count > 0) {
            if ($badge.length === 0) {
                // Cria o badge se não existir
                $bell.append('<span class="badge text-bg-primary notify-no rounded-circle">' + count + '</span>');
            } else {
                // Atualiza o badge existente
                $badge.text(count).show();
            }
        } else {
            // Remove o badge se não houver notificações
            $badge.hide();
        }
    }

    function updateNotificationList(notifications) {
        var $list = $('.notifications');
        $list.empty();

        if (notifications.length === 0) {
            $list.append('<div class="p-3 text-center text-muted">Nenhuma notificação encontrada</div>');
            return;
        }

        $.each(notifications, function(i, n) {
            var levelClass =
                n.level === 'error' ? 'danger' :
                (n.level === 'warning' ? 'warning' : 'info');

            // Formata a data de forma mais amigável
            var timeAgo = getTimeAgo(new Date(n.created_at));

            var item =
                '<a href="/Platform/system_notifications/' + n.id + '" ' +
                'class="message-item d-flex align-items-center border-bottom px-3 py-2 ' + (n.status === 'new' ? 'bg-light' : '') + '">' +
                    '<span class="btn btn-' + levelClass + ' rounded-circle btn-circle">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" ' +
                        'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
                        'stroke-linecap="round" stroke-linejoin="round" ' +
                        'class="feather feather-bell text-white">' +
                            '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>' +
                            '<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>' +
                        '</svg>' +
                    '</span>' +
                    '<div class="w-75 d-inline-block v-middle ps-2">' +
                        '<h6 class="message-title mb-0 mt-1 text-dark">' + escapeHtml(n.title) + '</h6>' +
                        '<span class="font-12 text-muted d-block text-truncate">' + escapeHtml(n.message) + '</span>' +
                        '<span class="font-12 text-muted d-block">' + timeAgo + '</span>' +
                    '</div>' +
                '</a>';

            $list.append(item);
        });
    }

    function getTimeAgo(date) {
        var now = new Date();
        var diff = Math.floor((now - date) / 1000); // diferença em segundos

        if (diff < 60) return 'agora';
        if (diff < 3600) return Math.floor(diff / 60) + ' min atrás';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h atrás';
        if (diff < 604800) return Math.floor(diff / 86400) + ' dias atrás';
        
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Atualiza ao carregar a página
    if (notificationsEnabled) {
        loadNotifications();

        // E repete no intervalo configurado
        notificationInterval = setInterval(loadNotifications, updateInterval);

        // Pausa a atualização quando a página não está visível (otimização)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(notificationInterval);
            } else {
                loadNotifications();
                notificationInterval = setInterval(loadNotifications, updateInterval);
            }
        });
    } else {
        // Se desabilitado, carrega apenas uma vez ao inicializar
        loadNotifications();
    }
});
</script>
@endpush
