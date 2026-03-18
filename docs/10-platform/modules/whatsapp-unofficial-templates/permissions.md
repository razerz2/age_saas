# Permissoes

Modulo base:

- `whatsapp_unofficial_templates`

Permissoes suportadas na policy:

- `whatsapp_unofficial_templates.view`
- `whatsapp_unofficial_templates.create`
- `whatsapp_unofficial_templates.edit`

Acoes adicionais de policy:

- `preview` (usa permissao `view`)
- `testSend` (usa permissao `edit`)

Regra:

- possuir o modulo base libera acesso;
- chaves granulares podem ser usadas como alternativa.
