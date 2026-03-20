# Modulo: Tenant Default Notification Templates

Baseline global de templates operacionais do Tenant, no dominio WhatsApp Nao Oficial da Platform.

Escopo:

- templates clinicos operacionais (`appointment.*`, `waitlist.*`);
- tabela separada da Platform (`tenant_default_notification_templates`);
- separado de `whatsapp_official_templates` (eventos SaaS oficiais da Platform).
- catalogo controlado: nao existe criacao manual via UI (sem rotas `create/store` e policy `create=false`);
- baseline populado via seeder (idempotente); apenas edicao/ativacao e permitida.

Keys baseline atuais:

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

Backfill administrativo:

- `php artisan tenants:seed-default-whatsapp-unofficial-templates` (dry-run por padrao)
- `--apply` para persistir
- `--tenant=<slug|uuid>` para alvo especifico
- `--overwrite` para atualizar existentes

Runtime integrado:

- o caminho central `NotificationDispatcher` agora resolve templates nao oficiais de WhatsApp com tenant-first;
- fallback Platform ocorre apenas com opt-in explicito;
- envio continua provider-agnostico (WAHA/Z-API) via `WhatsAppSender`/`WhatsAppService`.

Teste manual e preview:

- o teste manual operacional foi implementado no modulo `whatsapp-unofficial-templates` (templates internos Platform);
- este modulo (`tenant-default-notification-templates`) permanece como baseline/provisionamento.

Limite atual:

- campanhas e hardcodes legados fora do caminho central ainda nao foram migrados nesta etapa.

Arquivos: README.md, overview.md, routes.md, views.md, backend.md, frontend.md, database.md, permissions.md, troubleshooting.md
