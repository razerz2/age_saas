# Notifications — Rotas

Fontes: `routes/tenant.php` (bloco NOTIFICATIONS).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

Prefixo aproximado de rotas (conforme `routes/tenant.php`): `/workspace/{slug}/notifications`.

### Rotas

- `GET /workspace/{slug}/notifications`
  - Name: `tenant.notifications.index`
  - Controller: `Tenant\\NotificationController@index`

- `GET /workspace/{slug}/notifications/{notification}`
  - Name: `tenant.notifications.show`
  - Controller: `Tenant\\NotificationController@show`

- `POST /workspace/{slug}/notifications/{id}/read`
  - Name: `tenant.notifications.read`
  - Controller: `Tenant\\NotificationController@markAsRead`

- `POST /workspace/{slug}/notifications/mark-all-read`
  - Name: `tenant.notifications.mark-all-read`
  - Controller: `Tenant\\NotificationController@markAllAsRead`

- `GET /workspace/{slug}/notifications/json`
  - Name: `tenant.notifications.json`
  - Controller: `Tenant\\NotificationController@json`
