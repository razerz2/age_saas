# Permissoes

Modulo base:

- `tenant_default_notification_templates`

Permissoes suportadas na policy:

- `tenant_default_notification_templates.view`
- `tenant_default_notification_templates.edit`

Regra:

- possuir o modulo base ja libera acesso;
- chaves granulares podem ser usadas como alternativa.

Restricao intencional:

- `create` e sempre bloqueado na policy (catalogo controlado via seeder).
