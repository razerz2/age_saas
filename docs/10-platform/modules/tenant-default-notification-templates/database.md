# Database

Tabela principal (Platform):

- `tenant_default_notification_templates`

Campos principais:

- `channel` (`email|whatsapp`)
- `key`
- `title`
- `category`
- `language`
- `subject` (opcional)
- `content`
- `variables` (JSON)
- `is_active`

Indice unico:

- `(channel, key)`

Arquivos:

- migration: `database/migrations/2026_03_14_000100_create_tenant_default_notification_templates_table.php`
- model: `app/Models/Platform/TenantDefaultNotificationTemplate.php`
- catalogo: `app/Support/TenantDefaultNotificationTemplateCatalog.php`
- seeder: `database/seeders/TenantDefaultNotificationTemplatesSeeder.php`
