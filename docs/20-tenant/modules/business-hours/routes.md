# Business Hours — Rotas

Fontes: `routes/tenant.php` (bloco BUSINESS HOURS).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

- `GET /workspace/{slug}/business-hours`
  - Name: `tenant.business-hours.index`
  - Controller: `Tenant\\BusinessHourController@index`

- `GET /workspace/{slug}/business-hours/create`
  - Name: `tenant.business-hours.create`
  - Controller: `Tenant\\BusinessHourController@create`

- `POST /workspace/{slug}/business-hours`
  - Name: `tenant.business-hours.store`
  - Controller: `Tenant\\BusinessHourController@store`

- `GET /workspace/{slug}/business-hours/grid-data`
  - Name: `tenant.business-hours.grid-data`
  - Controller: `Tenant\\BusinessHourController@gridData`

- `GET /workspace/{slug}/business-hours/{id}`
  - Name: `tenant.business-hours.show`
  - Controller: `Tenant\\BusinessHourController@show`
  - Restrição: `{id}` é UUID (regex em `routes/tenant.php`).

- `GET /workspace/{slug}/business-hours/{id}/edit`
  - Name: `tenant.business-hours.edit`
  - Controller: `Tenant\\BusinessHourController@edit`
  - Restrição: `{id}` é UUID.

- `PUT /workspace/{slug}/business-hours/{id}`
  - Name: `tenant.business-hours.update`
  - Controller: `Tenant\\BusinessHourController@update`
  - Restrição: `{id}` é UUID.

- `DELETE /workspace/{slug}/business-hours/{id}`
  - Name: `tenant.business-hours.destroy`
  - Controller: `Tenant\\BusinessHourController@destroy`
  - Restrição: `{id}` é UUID.
