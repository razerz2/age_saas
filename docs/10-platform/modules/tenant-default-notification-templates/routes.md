# Rotas

Prefixo: `Platform/tenant-default-notification-templates`

Middleware:

- `auth`
- `module.access:tenant_default_notification_templates`

Rotas:

- `GET Platform.tenant-default-notification-templates.index`
- `GET Platform.tenant-default-notification-templates.edit`
- `PUT Platform.tenant-default-notification-templates.update`

Restricao intencional:

- rotas `create/store` nao existem neste modulo (catalogo controlado via seeder);
- policy `create` retorna `false`.
