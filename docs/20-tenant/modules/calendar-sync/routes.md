# Calendar Sync — Rotas

Fontes: `routes/tenant.php` (resource `calendar-sync`).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

- `GET /workspace/{slug}/calendar-sync`
  - Name: `tenant.calendar-sync.index`
  - Controller: `Tenant\\CalendarSyncStateController@index`

- `GET /workspace/{slug}/calendar-sync/create`
  - Name: `tenant.calendar-sync.create`
  - Controller: `Tenant\\CalendarSyncStateController@create`

- `POST /workspace/{slug}/calendar-sync`
  - Name: `tenant.calendar-sync.store`
  - Controller: `Tenant\\CalendarSyncStateController@store`

- `GET /workspace/{slug}/calendar-sync/{id}`
  - Name: `tenant.calendar-sync.show`
  - Controller: `Tenant\\CalendarSyncStateController@show`
  - Restrição: `{id}` é UUID (regex em `routes/tenant.php`).

- `GET /workspace/{slug}/calendar-sync/{id}/edit`
  - Name: `tenant.calendar-sync.edit`
  - Controller: `Tenant\\CalendarSyncStateController@edit`
  - Restrição: `{id}` é UUID.

- `PUT /workspace/{slug}/calendar-sync/{id}`
  - Name: `tenant.calendar-sync.update`
  - Controller: `Tenant\\CalendarSyncStateController@update`
  - Restrição: `{id}` é UUID.

- `DELETE /workspace/{slug}/calendar-sync/{id}`
  - Name: `tenant.calendar-sync.destroy`
  - Controller: `Tenant\\CalendarSyncStateController@destroy`
  - Restrição: `{id}` é UUID.
