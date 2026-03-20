# Database

Tabela (Platform):

- `notification_templates`

Scope do modulo:

- `channel = email`
- `scope = tenant`

Campos relevantes:

- `name` (key/evento)
- `display_name`
- `subject`
- `body`
- `default_subject`, `default_body` (usados no `Restaurar Padrao`)
- `variables` (JSON)
- `enabled`

Seeder:

- `database/seeders/NotificationTemplatesSeeder.php` (cria registros ausentes a partir de `tenant_default_notification_templates`).

