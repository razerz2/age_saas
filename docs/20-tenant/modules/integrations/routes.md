# Integrations — Rotas

Fontes: `routes/tenant.php` (bloco INTEGRATIONS).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

- `GET /workspace/{slug}/integrations`
  - Name: `tenant.integrations.index`
  - Controller: `Tenant\\IntegrationController@index`

- `GET /workspace/{slug}/integrations/create`
  - Name: `tenant.integrations.create`
  - Controller: `Tenant\\IntegrationController@create`

- `POST /workspace/{slug}/integrations`
  - Name: `tenant.integrations.store`
  - Controller: `Tenant\\IntegrationController@store`

- `GET /workspace/{slug}/integrations/{id}`
  - Name: `tenant.integrations.show`
  - Controller: `Tenant\\IntegrationController@show`

- `GET /workspace/{slug}/integrations/{id}/edit`
  - Name: `tenant.integrations.edit`
  - Controller: `Tenant\\IntegrationController@edit`

- `PUT /workspace/{slug}/integrations/{id}`
  - Name: `tenant.integrations.update`
  - Controller: `Tenant\\IntegrationController@update`

- `DELETE /workspace/{slug}/integrations/{id}`
  - Name: `tenant.integrations.destroy`
  - Controller: `Tenant\\IntegrationController@destroy`
