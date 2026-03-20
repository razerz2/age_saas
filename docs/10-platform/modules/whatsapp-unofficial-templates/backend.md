# Backend

Componentes principais:

- controller: `app/Http/Controllers/Platform/WhatsAppUnofficialTemplateController.php`
- servico de teste manual/preview: `app/Services/Platform/WhatsAppUnofficialTemplateManualTestService.php`
- requests:
  - `UpdateWhatsAppUnofficialTemplateRequest`
- policy: `app/Policies/Platform/WhatsAppUnofficialTemplatePolicy.php`
- model: `app/Models/Platform/WhatsAppUnofficialTemplate.php`
- catalogo baseline: `app/Support/WhatsAppUnofficialTemplateCatalog.php`
- seeder baseline: `database/seeders/WhatsAppUnofficialTemplatesSeeder.php`
- resolver de hierarquia (tenant/platform): `app/Services/Tenant/WhatsAppUnofficialTemplateResolutionService.php`
- gerador de dados ficticios: `app/Support/WhatsAppUnofficialTemplateFakeDataFactory.php`

Observacao:

- catalogo controlado: sem criacao manual via UI (sem rotas `create/store` e policy `create=false`);
- operacoes expostas: list/show/edit/update/preview/test-send/toggle;
- baseline populado por seeder idempotente via `upsert` por `key`.

Hierarquia implementada no resolver:

1. `tenant.notification_templates` (tenant)
2. `whatsapp_unofficial_templates` (Platform) apenas com fallback explicito (`tenant_then_platform`)

Integracao no runtime nao oficial (etapa atual):

- ponto central de resolucao/renderizacao: `app/Services/Tenant/NotificationDispatcher.php`
- envio canonico provider-agnostico: `app/Services/Tenant/WhatsAppSender.php`
- handoff para provider: `app/Services/WhatsappTenantService.php` -> `app/Services/WhatsAppService.php`
- providers suportados sem acoplamento de template: WAHA (`WahaProvider`) e Z-API (`ZApiProvider`)

Logs tecnicos adicionados no envio nao oficial:

- `key`
- `template_source`
- `template_resolution_scope`
- `used_platform_fallback`
- `template_fallback_reason`
- `provider`
- `to_masked`
- `sent`/erro

Logs tecnicos do teste manual nao oficial:

- `template_key`
- `template_scope` (`platform_unofficial`)
- `provider`
- `to_masked`
- `preview_source`
- `result`
- `error` resumido (quando houver)

Limites desta etapa:

- integrado no caminho central baseado em key/template (`NotificationDispatcher`);
- fluxos legados hardcoded fora deste caminho ainda permanecem para migracao incremental.
