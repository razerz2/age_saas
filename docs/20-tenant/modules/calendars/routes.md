# Calendars — Rotas

Fontes: `routes/tenant.php` (bloco CALENDARS).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

- `GET /workspace/{slug}/calendars`
  - Name: `tenant.calendars.index`
  - Controller: `Tenant\CalendarController@index`

- `GET /workspace/{slug}/calendars/create`
  - Name: `tenant.calendars.create`
  - Controller: `Tenant\CalendarController@create`

- `POST /workspace/{slug}/calendars`
  - Name: `tenant.calendars.store`
  - Controller: `Tenant\CalendarController@store`

- `GET /workspace/{slug}/calendars/grid-data`
  - Name: `tenant.calendars.grid-data`
  - Controller: `Tenant\CalendarController@gridData`

- `GET /workspace/{slug}/calendars/{id}`
  - Name: `tenant.calendars.show`
  - Controller: `Tenant\CalendarController@show`
  - Restrição: `{id}` é UUID (regex em `routes/tenant.php`).

- `GET /workspace/{slug}/calendars/{id}/edit`
  - Name: `tenant.calendars.edit`
  - Controller: `Tenant\CalendarController@edit`
  - Restrição: `{id}` é UUID.

- `PUT /workspace/{slug}/calendars/{id}`
  - Name: `tenant.calendars.update`
  - Controller: `Tenant\CalendarController@update`
  - Restrição: `{id}` é UUID.

- `DELETE /workspace/{slug}/calendars/{id}`
  - Name: `tenant.calendars.destroy`
  - Controller: `Tenant\CalendarController@destroy`
  - Restrição: `{id}` é UUID.

- `GET /workspace/{slug}/calendars/events`
  - Name: `tenant.calendars.events.redirect`
  - Controller: `Tenant\CalendarController@eventsRedirect`

- `GET /workspace/{slug}/calendars/{id}/events`
  - Name: `tenant.calendars.events`
  - Controller: `Tenant\AppointmentController@events`
  - Restrição: `{id}` é UUID.
