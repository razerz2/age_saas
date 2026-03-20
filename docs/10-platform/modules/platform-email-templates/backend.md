# Backend

Componentes principais:

- controller: `app/Http/Controllers/Platform/PlatformEmailTemplateController.php`
- model: `app/Models/Platform/PlatformEmailTemplate.php` (scope `platform`, channel `email`)
- base model: `app/Models/Platform/NotificationTemplate.php`
- request (update): `app/Http/Requests/Platform/UpdatePlatformEmailTemplateRequest.php`
- policy: `app/Policies/Platform/PlatformEmailTemplatePolicy.php` (bloqueia `create`)

Teste de envio:

- service: `app/Services/Platform/EmailTemplateTestSendService.php`
- renderer: `app/Services/Tenant/TemplateRenderer.php`
- comportamento: extrai placeholders, monta contexto dummy e substitui placeholders desconhecidos por valores de teste.

Seeding (origem do baseline):

- tabela fonte: `whatsapp_unofficial_templates`
- seeder: `database/seeders/NotificationTemplatesSeeder.php`
- idempotencia: usa `firstOrCreate` por `(scope, channel, name)` (nao duplica; nao sobrescreve edicoes).

