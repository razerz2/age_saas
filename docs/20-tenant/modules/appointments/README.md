# Módulo: Appointments (Tenant)

Documentação do módulo de agendamentos interno e público, incluindo:

- Confirmação com prazo (hold) + tokens (confirm/cancel) + expiração
- Waitlist (fila) por slot + oferta/aceite + expiração de ofertas
- Slots com status `FREE/HOLD/BUSY/DISABLED` (+ `reason`)
- UI interna e pública: seleção de horários ocupados + `intent_waitlist`
- Jobs/commands e scheduler

Importante:

- O escopo aqui **não** inclui implementação no módulo de agendamento recorrente.

## Arquivos

- `overview.md`: visão geral, conceitos, status e fluxos.
- `routes.md`: rotas internas/públicas e APIs de slots/waitlist/confirmação.
- `backend.md`: controllers, requests, service, jobs e scheduler.
- `frontend.md`: comportamento das telas JS/Blade (interno e público).
- `database.md`: migrations, tabelas, colunas, índices e models.
- `permissions.md`: impacto de permissão/acesso.
- `troubleshooting.md`: diagnóstico, comandos operacionais e checklist de regressão.

## Cross-references

- Editor de templates + auditoria de entregas: `docs/20-tenant/modules/notification-templates/README.md`

