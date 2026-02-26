# Campanhas — Permissions

Fontes:

- `resources/views/layouts/tailadmin/sidebar.blade.php`
- `routes/tenant.php`
- `app/Http/Kernel.php`
- `app/Http/Middleware/Tenant/EnsureCampaignModuleEnabled.php`
- `app/Services/Tenant/CampaignChannelGate.php`

## Controle de acesso no projeto (como está hoje)

### Menu / acesso por módulo do usuário

O menu do Tenant controla a exibição do item **Campanhas** com:

- Admin: `($user && $user->role === 'admin')`
- Ou módulo habilitado no usuário: `in_array('campaigns', $userModules)`

Implementação: `resources/views/layouts/tailadmin/sidebar.blade.php` (variável `$hasCampaignsAccess`).

> Observação factual: esse controle é de UI/menu. Ele não substitui middlewares/guards do grupo de rotas.

### Guard e middlewares do grupo Tenant

As rotas vivem sob `/workspace/{slug}` e usam o guard/middlewares do Tenant (ver `docs/20-tenant/02-rotas.md` e `routes/tenant.php`).

## Gating funcional por integrações (canais)

Mesmo com acesso à UI, ações do módulo dependem de canais disponíveis no tenant:

- `CampaignChannelGate::availableChannels()` retorna canais habilitados (`email`, `whatsapp`) conforme `TenantSetting`.
- `CampaignChannelGate::assertChannelsEnabled(...)` lança `DomainException` quando:
  - nenhum canal está disponível, ou
  - o canal solicitado não está disponível.

## Middleware aplicado no módulo

Alias:

- `campaign.module.enabled` → `App\Http\Middleware\Tenant\EnsureCampaignModuleEnabled` (registrado em `app/Http/Kernel.php`)

Efeito:

- Quando `CampaignChannelGate::availableChannels() === []`:
  - Para requests HTML: redireciona e seta flash `warning`.
  - Para requests JSON: responde `403` com `{message: ...}`.

Rotas protegidas por `campaign.module.enabled` (conforme `routes/tenant.php`):

- `tenant.campaigns.create`
- `tenant.campaigns.store`
- `tenant.campaigns.assets.store`
- `tenant.campaigns.sendTest`
- `tenant.campaigns.start`
- `tenant.campaigns.schedule`
- `tenant.campaigns.pause`
- `tenant.campaigns.resume`
- `tenant.campaigns.edit`
- `tenant.campaigns.update`
- `tenant.campaigns.destroy`

Rotas que não aplicam esse middleware hoje (mas podem desabilitar ações na UI):

- `tenant.campaigns.index`
- `tenant.campaigns.grid`
- `tenant.campaigns.show`
- `tenant.campaigns.runs.index` / `tenant.campaigns.runs.grid`
- `tenant.campaigns.recipients.index` / `tenant.campaigns.recipients.grid`

