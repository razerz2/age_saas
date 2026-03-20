# Routes

Prefixo: `Platform/platform-email-templates`

Middleware:

- `auth`
- `module.access:platform_email_templates`

Rotas:

- `GET Platform.platform-email-templates.index`
- `GET Platform.platform-email-templates.show`
- `GET Platform.platform-email-templates.edit`
- `PUT Platform.platform-email-templates.update`
- `POST Platform.platform-email-templates.restore`
- `POST Platform.platform-email-templates.toggle`
- `POST Platform.platform-email-templates.test-send`

Restricao intencional:

- rotas `create/store` nao existem (catalogo controlado via seeder);
- policy `create` retorna `false`.

