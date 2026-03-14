# Backend

Componentes principais:

- controller: `app/Http/Controllers/Platform/TenantDefaultNotificationTemplateController.php`
- requests:
  - `StoreTenantDefaultNotificationTemplateRequest`
  - `UpdateTenantDefaultNotificationTemplateRequest`
- policy: `app/Policies/Platform/TenantDefaultNotificationTemplatePolicy.php`
- model: `app/Models/Platform/TenantDefaultNotificationTemplate.php`

Provisionamento:

- service: `app/Services/Platform/TenantDefaultNotificationTemplateProvisioningService.php`
- integrado em: `app/Services/TenantProvisioner.php`

Backfill administrativo:

- comando: `php artisan tenants:seed-default-notification-templates`
- modo padrao: dry-run
- aplicar: `--apply`
- atualizar existentes: `--overwrite`

