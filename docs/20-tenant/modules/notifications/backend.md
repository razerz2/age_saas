# Notifications — Backend

Fontes: `app/Http/Controllers/Tenant/NotificationController.php`.

## Controller

- `App\Http\Controllers\Tenant\NotificationController`
  - Métodos públicos:
    - `index()`
    - `show($slug, $notificationId)`
    - `markAsRead($slug, $id)`
    - `markAllAsRead()`
    - `json()`

## Models e serviços

- `App\Models\Tenant\Notification`
- `App\Services\TenantNotificationService`

## Fluxo geral (factual)

- `index`:
  - Lista `Notification` ordenadas por `created_at` desc e paginação (20).
  - Envia para a view `tenant.notifications.index`.

- `show`:
  - Carrega `Notification` por id.
  - Se status é `new`, chama `markAsRead()` antes de exibir.
  - Envia para `tenant.notifications.show`.

- `markAsRead`:
  - Carrega `Notification` por id.
  - Chama `markAsRead()` e retorna JSON `{'success': true}`.

- `markAllAsRead`:
  - Chama `TenantNotificationService::markAllAsRead()`.
  - Retorna JSON com `success` e `count` de notificações afetadas.

- `json`:
  - Carrega até 10 notificações ordenadas por `created_at` desc.
  - Usa `TenantNotificationService::unreadCount()` para obter contagem de não lidas.
  - Retorna JSON com `unread_count` e coleção de `notifications`.
