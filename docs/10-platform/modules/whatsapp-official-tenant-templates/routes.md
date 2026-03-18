# Routes

Prefixo: `/Platform/whatsapp-official-tenant-templates`

- `GET /` -> `Platform.whatsapp-official-tenant-templates.index`
- `GET /create` -> `Platform.whatsapp-official-tenant-templates.create`
- `POST /` -> `Platform.whatsapp-official-tenant-templates.store`
- `GET /{whatsappOfficialTemplate}` -> `Platform.whatsapp-official-tenant-templates.show`
- `GET /{whatsappOfficialTemplate}/edit` -> `Platform.whatsapp-official-tenant-templates.edit`
- `PUT /{whatsappOfficialTemplate}` -> `Platform.whatsapp-official-tenant-templates.update`
- `POST /{whatsappOfficialTemplate}/submit` -> `Platform.whatsapp-official-tenant-templates.submit`
- `POST /{whatsappOfficialTemplate}/sync` -> `Platform.whatsapp-official-tenant-templates.sync`
- `POST /{whatsappOfficialTemplate}/test-send` -> `Platform.whatsapp-official-tenant-templates.test-send`

Middlewares:

- `auth`
- `module.access:whatsapp_official_tenant_templates`
- `whatsapp.official.provider`
- policies (`can:*`) por acao
