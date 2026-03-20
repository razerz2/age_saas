# Rotas

Prefixo: `Platform/whatsapp-unofficial-templates`

Middleware:

- `auth`
- `module.access:whatsapp_unofficial_templates`

Rotas:

- `GET Platform.whatsapp-unofficial-templates.index`
- `GET Platform.whatsapp-unofficial-templates.show`
- `GET Platform.whatsapp-unofficial-templates.edit`
- `PUT Platform.whatsapp-unofficial-templates.update`
- `POST Platform.whatsapp-unofficial-templates.preview`
- `POST Platform.whatsapp-unofficial-templates.test-send`
- `POST Platform.whatsapp-unofficial-templates.toggle`

Restricao intencional:

- rotas `create/store` nao existem neste modulo (catalogo controlado via seeder);
- policy `create` retorna `false`.
