# Backend

Componentes principais:

- controller: `app/Http/Controllers/Platform/TenantEmailTemplateController.php`
- model: `app/Models/Platform/TenantEmailTemplate.php` (scope `tenant`, channel `email`)
- base model: `app/Models/Platform/NotificationTemplate.php`
- request (update): `app/Http/Requests/Platform/UpdateTenantEmailTemplateRequest.php`
- policy: `app/Policies/Platform/TenantEmailTemplatePolicy.php` (bloqueia `create`)

Teste de envio:

- service: `app/Services/Platform/EmailTemplateTestSendService.php`
- renderer: `app/Services/Tenant/TemplateRenderer.php`
- comportamento: extrai placeholders, monta contexto dummy e substitui placeholders desconhecidos por valores de teste.

Seeding (origem do baseline):

- tabela fonte: `tenant_default_notification_templates` (canal `whatsapp`)
- seeder: `database/seeders/NotificationTemplatesSeeder.php`
- idempotencia: usa `firstOrCreate` por `(scope, channel, name)` (nao duplica; nao sobrescreve edicoes).

