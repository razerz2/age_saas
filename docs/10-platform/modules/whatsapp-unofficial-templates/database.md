# Database

Tabela principal (Platform):

- `whatsapp_unofficial_templates`

Campos principais:

- `key` (unico)
- `title`
- `category`
- `body`
- `variables` (JSON)
- `is_active`
- `timestamps`

Arquivos:

- migration: `database/migrations/2026_03_14_000200_create_whatsapp_unofficial_templates_table.php`
- model: `app/Models/Platform/WhatsAppUnofficialTemplate.php`
- catalogo: `app/Support/WhatsAppUnofficialTemplateCatalog.php`
- seeder: `database/seeders/WhatsAppUnofficialTemplatesSeeder.php`
