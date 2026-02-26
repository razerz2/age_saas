# Campanhas — Rotas

Fontes: `routes/tenant.php` (bloco CAMPAIGNS).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Name base: `tenant.`
- Middlewares do grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

Prefixo aproximado: `/workspace/{slug}/campaigns`.

> Nota: a rota `campaigns/grid-data` vem **antes** de `campaigns/{campaign}` para evitar conflito de match (sem isso, `{campaign}` poderia capturar `grid-data`).

### Listagem e CRUD

- `GET /workspace/{slug}/campaigns`
  - Name: `tenant.campaigns.index`
  - Controller: `Tenant\\CampaignController@index`

- `GET /workspace/{slug}/campaigns/grid-data`
  - Name: `tenant.campaigns.grid`
  - Controller: `Tenant\\CampaignController@gridData`

- `GET /workspace/{slug}/campaigns/create`
  - Name: `tenant.campaigns.create`
  - Controller: `Tenant\\CampaignController@create`
  - Middleware: `campaign.module.enabled`

- `POST /workspace/{slug}/campaigns`
  - Name: `tenant.campaigns.store`
  - Controller: `Tenant\\CampaignController@store`
  - Middleware: `campaign.module.enabled`

- `GET /workspace/{slug}/campaigns/{campaign}`
  - Name: `tenant.campaigns.show`
  - Controller: `Tenant\\CampaignController@show`

- `GET /workspace/{slug}/campaigns/{campaign}/edit`
  - Name: `tenant.campaigns.edit`
  - Controller: `Tenant\\CampaignController@edit`
  - Middleware: `campaign.module.enabled`

- `PUT /workspace/{slug}/campaigns/{campaign}`
  - Name: `tenant.campaigns.update`
  - Controller: `Tenant\\CampaignController@update`
  - Middleware: `campaign.module.enabled`

- `DELETE /workspace/{slug}/campaigns/{campaign}`
  - Name: `tenant.campaigns.destroy`
  - Controller: `Tenant\\CampaignController@destroy`
  - Middleware: `campaign.module.enabled`

### Assets (upload)

- `POST /workspace/{slug}/campaigns/assets`
  - Name: `tenant.campaigns.assets.store`
  - Controller: `Tenant\\CampaignAssetController@store`
  - Middleware: `campaign.module.enabled`

### Disparo manual (ações)

- `POST /workspace/{slug}/campaigns/{campaign}/send-test`
  - Name: `tenant.campaigns.sendTest`
  - Controller: `Tenant\\CampaignDispatchController@sendTest`
  - Middleware: `campaign.module.enabled`

- `POST /workspace/{slug}/campaigns/{campaign}/start`
  - Name: `tenant.campaigns.start`
  - Controller: `Tenant\\CampaignDispatchController@start`
  - Middleware: `campaign.module.enabled`

- `POST /workspace/{slug}/campaigns/{campaign}/schedule`
  - Name: `tenant.campaigns.schedule`
  - Controller: `Tenant\\CampaignDispatchController@schedule`
  - Middleware: `campaign.module.enabled`

- `POST /workspace/{slug}/campaigns/{campaign}/pause`
  - Name: `tenant.campaigns.pause`
  - Controller: `Tenant\\CampaignDispatchController@pause`
  - Middleware: `campaign.module.enabled`

- `POST /workspace/{slug}/campaigns/{campaign}/resume`
  - Name: `tenant.campaigns.resume`
  - Controller: `Tenant\\CampaignDispatchController@resume`
  - Middleware: `campaign.module.enabled`

### Histórico: Runs

- `GET /workspace/{slug}/campaigns/{campaign}/runs`
  - Name: `tenant.campaigns.runs.index`
  - Controller: `Tenant\\CampaignRunController@index`

- `GET /workspace/{slug}/campaigns/{campaign}/runs/grid-data`
  - Name: `tenant.campaigns.runs.grid`
  - Controller: `Tenant\\CampaignRunController@gridData`

### Histórico: Recipients

- `GET /workspace/{slug}/campaigns/{campaign}/recipients`
  - Name: `tenant.campaigns.recipients.index`
  - Controller: `Tenant\\CampaignRecipientController@index`

- `GET /workspace/{slug}/campaigns/{campaign}/recipients/grid-data`
  - Name: `tenant.campaigns.recipients.grid`
  - Controller: `Tenant\\CampaignRecipientController@gridData`

