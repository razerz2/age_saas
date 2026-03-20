# Tenant - Rotas (Indice por Modulo)

Este arquivo funciona como **indice** das rotas da area Tenant ja detalhadas por modulo.

## Organizacao geral

- Prefixos principais:
  - `/customer/{slug}` -> login, area publica de agendamento e formularios publicos.
  - `/workspace/{slug}` -> area autenticada do tenant (rotas mapeadas pelos modulos abaixo).
- Guard principal: `tenant` (com middlewares como `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`).
- Bloqueio comercial na area autenticada: middleware `tenant.commercial.eligibility` no grupo `/workspace/{slug}`.

## Regra de acesso comercial (workspace)

Antes de usar qualquer rota autenticada em `/workspace/{slug}`, o sistema valida `Tenant::isEligibleForAccess()`.

- Elegivel: acesso continua normalmente.
- Nao elegivel: acesso bloqueado e redirecionamento para `tenant.login` com mensagem orientando configuracao comercial.

Importante:

- `tenants.plan_id` isolado nao libera acesso.
- A validacao usa assinatura ativa + plano vinculado pela assinatura.
- O bloqueio nao se aplica a Platform nem a rotas publicas da landing.

As definicoes completas vivem em `routes/tenant.php`. Os detalhes por modulo estao nos arquivos em `docs/20-tenant/modules/*/routes.md`.

## Rotas por modulo

- **Appointments**
  - Rotas autenticadas para CRUD de agendamentos internos (`/workspace/{slug}/appointments/*`) e APIs auxiliares (`/workspace/{slug}/api/doctors/...`).
  - Detalhamento em: `docs/20-tenant/modules/appointments/routes.md`.

- **Recurring Appointments**
  - Rotas dedicadas de recorrencia (`/workspace/{slug}/agendamentos/recorrentes/*`) e APIs de horarios disponiveis para series recorrentes.
  - Detalhamento em: `docs/20-tenant/modules/recurring-appointments/routes.md`.

- **Forms**
  - Rotas para CRUD de formularios (`/workspace/{slug}/forms/*`), builder, preview e endpoints internos de secoes/perguntas/opcoes.
  - Detalhamento em: `docs/20-tenant/modules/forms/routes.md`.

- **Form Responses**
  - Rotas para listar, responder e revisar respostas de formularios (`/workspace/{slug}/responses/*`) e grid-data.
  - Detalhamento em: `docs/20-tenant/modules/form-responses/routes.md`.

- **Business Hours**
  - Rotas para gerenciar horarios de atendimento por medico e dia da semana (`/workspace/{slug}/business-hours/*`) e endpoint `grid-data` para listagem.
  - Detalhamento em: `docs/20-tenant/modules/business-hours/routes.md`.

- **Appointment Types**
  - Rotas para gerenciar tipos de atendimento por medico (`/workspace/{slug}/appointment-types/*`) e endpoint `grid-data` para listagem.
  - Detalhamento em: `docs/20-tenant/modules/appointment-types/routes.md`.

- **Calendar Sync**
  - Rotas para gerenciar estados de sincronizacao de calendario (`/workspace/{slug}/calendar-sync/*`).
  - Detalhamento em: `docs/20-tenant/modules/calendar-sync/routes.md`.

- **Online Appointments**
  - Rotas para gerenciar agendamentos online (`/workspace/{slug}/appointments/online/*`) e endpoint `grid-data`.
  - Detalhamento em: `docs/20-tenant/modules/online-appointments/routes.md`.

- **Notifications**
  - Rotas para listar, visualizar e marcar notificacoes como lidas (`/workspace/{slug}/notifications/*`) e endpoint JSON.
  - Detalhamento em: `docs/20-tenant/modules/notifications/routes.md`.

- **Campaigns**
  - Rotas autenticadas para CRUD e execucao de campanhas (`/workspace/{slug}/campaigns/*`), incluindo:
    - `grid-data` (Grid.js server-side)
    - upload de assets (`/campaigns/assets`)
    - acoes de disparo (`send-test`, `start`, `schedule`, `pause`, `resume`)
    - historico (runs e recipients)
  - Detalhamento em: `docs/20-tenant/modules/campaigns/routes.md`.

- **Integrations**
  - Rotas para CRUD de integracoes (`/workspace/{slug}/integrations/*`).
  - Detalhamento em: `docs/20-tenant/modules/integrations/routes.md`.

- **Public Customer**
  - Rotas publicas sob `/customer/{slug}` para identificacao/cadastro de paciente, agendamento publico e formularios publicos.
  - Detalhamento em: `docs/20-tenant/modules/public-customer/routes.md`.

- **Medical Appointments**
  - Rotas de sessao de atendimento medico do dia (`/workspace/{slug}/atendimento/*`).
  - Detalhamento em: `docs/20-tenant/modules/medical-appointments/routes.md`.

> Para a visao mais ampla de rotas do Tenant (incluindo portal do paciente e area publica), consulte tambem `TENANT.md` e `ARQUITETURA.md`.
