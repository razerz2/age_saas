# Permissoes

Middleware:

- `module.access:platform_email_templates`

Policy:

- `app/Policies/Platform/PlatformEmailTemplatePolicy.php`
- `create` e sempre `false` (catalogo controlado)
- `view/viewAny` e `update/restore/toggle` dependem de `platform_email_templates` (ou chaves granulares).

