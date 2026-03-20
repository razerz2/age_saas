# Routes

Modulo de layouts (Platform):

Prefixo: `Platform/email-layouts`

Middleware:

- `auth`
- `module.access:notification_templates`

Rotas:

- `GET Platform.email-layouts.index`
- `GET Platform.email-layouts.edit`
- `PUT Platform.email-layouts.update`
- `GET Platform.email-layouts.preview`

Restricao:

- nao existe criacao manual de layouts via rotas (apenas editar/ativar).
