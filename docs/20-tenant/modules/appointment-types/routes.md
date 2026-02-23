# Appointment Types — Rotas

Fontes: `routes/tenant.php` (bloco APPOINTMENT TYPES).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

- `GET /workspace/{slug}/appointment-types`
  - Name: `tenant.appointment-types.index`
  - Controller: `Tenant\\AppointmentTypeController@index`

- `GET /workspace/{slug}/appointment-types/create`
  - Name: `tenant.appointment-types.create`
  - Controller: `Tenant\\AppointmentTypeController@create`

- `POST /workspace/{slug}/appointment-types`
  - Name: `tenant.appointment-types.store`
  - Controller: `Tenant\\AppointmentTypeController@store`

- `GET /workspace/{slug}/appointment-types/grid-data`
  - Name: `tenant.appointment-types.grid-data`
  - Controller: `Tenant\\AppointmentTypeController@gridData`

- `GET /workspace/{slug}/appointment-types/{id}`
  - Name: `tenant.appointment-types.show`
  - Controller: `Tenant\\AppointmentTypeController@show`
  - Restrição: `{id}` é UUID (regex em `routes/tenant.php`).

- `GET /workspace/{slug}/appointment-types/{id}/edit`
  - Name: `tenant.appointment-types.edit`
  - Controller: `Tenant\\AppointmentTypeController@edit`
  - Restrição: `{id}` é UUID.

- `PUT /workspace/{slug}/appointment-types/{id}`
  - Name: `tenant.appointment-types.update`
  - Controller: `Tenant\\AppointmentTypeController@update`
  - Restrição: `{id}` é UUID.

- `DELETE /workspace/{slug}/appointment-types/{id}`
  - Name: `tenant.appointment-types.destroy`
  - Controller: `Tenant\\AppointmentTypeController@destroy`
  - Restrição: `{id}` é UUID.
