# Módulo: Notification Templates (Tenant)

Documentação do Editor de Templates de Notificação (email/whatsapp), catálogo default do sistema, overrides por tenant e auditoria persistente de entregas.

Este módulo cobre:

- Catálogo default: `config/notification_templates.php`
- Overrides por tenant: `notification_templates`
- Editor UI: Settings > Editor (save/restore/preview)
- Renderização de placeholders: `{{dot.notation}}`
- Contexto padronizado: agendamento, clínica e links públicos
- Dispatch/entrega real: WhatsApp/Email usando templates efetivos
- Auditoria persistente: `notification_deliveries` (LGPD: hash/length por padrão)

## Arquivos

- `overview.md`: visão geral (default vs override, canais e keys).
- `routes.md`: rotas do Editor (save/restore/preview) e URL com querystring.
- `backend.md`: services (renderer/context/dispatcher/templates) e senders.
- `frontend.md`: layout do Editor, botões, preview e avisos.
- `views.md`: views/blades envolvidas no Editor e preview.
- `database.md`: tabelas `notification_templates` e `notification_deliveries` + flags LGPD.
- `permissions.md`: acesso/permissões para operar o Editor.
- `troubleshooting.md`: diagnóstico (override, placeholders, preview, entregas).

## Cross-references

- Agendamentos (hold/waitlist/keys disparadas): `docs/20-tenant/modules/appointments/backend.md`
