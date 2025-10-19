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
         style="min-width: 320px; transform: translateX(-60%)">
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

    function loadNotifications() {
        $.ajax({
            url: "{{ route('Platform.system_notifications.json') }}",
            type: "GET",
            dataType: "json",
            success: function(data) {

                // Atualiza badge de contagem
                var $badge = $('.notify-no');
                if (data.unread_count > 0) {
                    $badge.text(data.unread_count).show();
                } else {
                    $badge.hide();
                }

                // Atualiza lista de notificações no dropdown
                var $list = $('.notifications');
                $list.empty();

                if (data.notifications.length === 0) {
                    $list.append('<div class="p-3 text-center text-muted">Nenhuma notificação</div>');
                    return;
                }

                $.each(data.notifications, function(i, n) {
                    var levelClass =
                        n.level === 'error' ? 'danger' :
                        (n.level === 'warning' ? 'warning' : 'info');

                    var item =
                        '<a href="/Platform/system_notifications/' + n.id + '" ' +
                        'class="message-item d-flex align-items-center border-bottom px-3 py-2 ' + (n.status === 'new' ? 'bg-light' : '') + '">' +
                            '<span class="btn btn-' + levelClass + ' rounded-circle btn-circle">' +
                                '<i class="fa fa-bell text-white"></i>' +
                            '</span>' +
                            '<div class="w-75 d-inline-block v-middle ps-2">' +
                                '<h6 class="message-title mb-0 mt-1 text-dark">' + n.title + '</h6>' +
                                '<span class="font-12 text-muted d-block text-truncate">' + n.message + '</span>' +
                                '<span class="font-12 text-muted d-block">' + new Date(n.created_at).toLocaleString('pt-BR') + '</span>' +
                            '</div>' +
                        '</a>';

                    $list.append(item);
                });
            },
            error: function(xhr, status, error) {
                console.error('Erro ao buscar notificações:', error);
            }
        });
    }

    // Atualiza ao carregar a página
    loadNotifications();

    // E repete a cada 5 segundos
    setInterval(loadNotifications, 5000);
});
</script>
@endpush
