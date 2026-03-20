# Routes

Prefixo: `Platform/tenant-email-templates`

Middleware:

- `auth`
- `module.access:tenant_email_templates`

Rotas:

- `GET Platform.tenant-email-templates.index`
- `GET Platform.tenant-email-templates.show`
- `GET Platform.tenant-email-templates.edit`
- `PUT Platform.tenant-email-templates.update`
- `POST Platform.tenant-email-templates.restore`
- `POST Platform.tenant-email-templates.toggle`
- `POST Platform.tenant-email-templates.test-send`

Restricao intencional:

- rotas `create/store` nao existem (catalogo controlado via seeder);
- policy `create` retorna `false`.

