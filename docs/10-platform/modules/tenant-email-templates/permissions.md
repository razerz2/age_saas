# Permissoes

Middleware:

- `module.access:tenant_email_templates`

Policy:

- `app/Policies/Platform/TenantEmailTemplatePolicy.php`
- `create` e sempre `false` (catalogo controlado)
- `view/viewAny` e `update/restore/toggle` dependem de `tenant_email_templates` (ou chaves granulares).

