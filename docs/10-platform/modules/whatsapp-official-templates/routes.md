# Routes

Prefixo: `/Platform/whatsapp-official-templates`

- `GET /` -> `Platform.whatsapp-official-templates.index`
- `GET /create` -> `Platform.whatsapp-official-templates.create`
- `POST /` -> `Platform.whatsapp-official-templates.store`
- `GET /{whatsappOfficialTemplate}` -> `Platform.whatsapp-official-templates.show`
- `GET /{whatsappOfficialTemplate}/edit` -> `Platform.whatsapp-official-templates.edit`
- `PUT /{whatsappOfficialTemplate}` -> `Platform.whatsapp-official-templates.update`
- `POST /{whatsappOfficialTemplate}/duplicate` -> `Platform.whatsapp-official-templates.duplicate`
- `POST /{whatsappOfficialTemplate}/submit` -> `Platform.whatsapp-official-templates.submit`
- `POST /{whatsappOfficialTemplate}/sync` -> `Platform.whatsapp-official-templates.sync`
- `POST /{whatsappOfficialTemplate}/archive` -> `Platform.whatsapp-official-templates.archive`
- `POST /{whatsappOfficialTemplate}/test-send` -> `Platform.whatsapp-official-templates.test-send`

Middlewares:

- `auth`
- `module.access:whatsapp_official_templates`
- `whatsapp.official.provider`
- policies (`can:*`) por acao
