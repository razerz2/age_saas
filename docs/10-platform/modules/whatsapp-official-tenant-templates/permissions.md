# Permissions

Policy:

- `App\Policies\Platform\WhatsAppOfficialTenantTemplatePolicy`

Escopo do modulo:

- baseline oficial tenant (keys clinicas) dentro do catalogo global `whatsapp_official_templates`
- a policy controla acesso/acoes para esse recorte de dominio

Abilidades:

- `viewAny`
- `view`
- `create`
- `update`
- `submitToMeta`
- `syncStatus`
- `testSend`

Chaves de modulo aceitas:

- `whatsapp_official_tenant_templates` (ou abilities granulares)
- fallback de compatibilidade: `whatsapp_official_templates`

Middleware de rota:

- `module.access:whatsapp_official_tenant_templates`
