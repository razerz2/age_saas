# Backend

Componentes principais:

- controller: `app/Http/Controllers/Platform/TenantDefaultNotificationTemplateController.php`
- requests:
  - `StoreTenantDefaultNotificationTemplateRequest`
  - `UpdateTenantDefaultNotificationTemplateRequest`
- policy: `app/Policies/Platform/TenantDefaultNotificationTemplatePolicy.php`
- model: `app/Models/Platform/TenantDefaultNotificationTemplate.php`
- catalogo baseline: `app/Support/TenantDefaultNotificationTemplateCatalog.php`
- seeder baseline: `database/seeders/TenantDefaultNotificationTemplatesSeeder.php`

Dominio:

- modulo posicionado no agrupamento `WhatsApp Nao Oficial` da navegacao Platform.

Provisionamento:

- service: `app/Services/Platform/TenantDefaultNotificationTemplateProvisioningService.php`
- integrado em: `app/Services/TenantProvisioner.php`
- fluxo de criacao: `TenantCreatorService` / `PreTenantProcessorService` -> `TenantProvisioner::createDatabase()` -> `syncForTenant(...)`
- politica padrao no provisionamento: inserir ausentes e nao sobrescrever existentes (`overwrite=false`)

Backfill administrativo:

- comando principal: `php artisan tenants:seed-default-whatsapp-unofficial-templates`
- comando legado (compatibilidade): `php artisan tenants:seed-default-notification-templates`
- modo padrao: dry-run
- aplicar: `--apply`
- tenant especifico: `--tenant=<slug|uuid>`
- atualizar existentes: `--overwrite`
- seeding idempotente via `upsert` por `(channel, key)`.

Runtime nao oficial conectado nesta etapa:

- servico central: `app/Services/Tenant/NotificationDispatcher.php`
- para canal `whatsapp`, o dispatcher usa `WhatsAppUnofficialTemplateResolutionService`
- resolucao continua tenant-first e usa fallback Platform apenas com opt-in (`template_resolution_scope=tenant_then_platform` ou `allow_platform_fallback=true`)
- renderizacao continua na engine existente (`TemplateRenderer`)
- envio final continua canonico em `WhatsAppSender` (agnostico a provider)

Compatibilidade:

- quando a key nao estiver no novo catalogo resolvido, o dispatcher mantem fallback seguro para `NotificationTemplateService`/catalogo legado;
- fluxos fora do caminho central ainda nao foram migrados nesta etapa.

Observacao de UX/operacao:

- teste manual + preview ficam no modulo `whatsapp-unofficial-templates` (catalogo interno Platform);
- este modulo concentra baseline tenant e administracao do conteudo padrao de provisionamento.
