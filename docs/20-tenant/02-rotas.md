# Tenant — Rotas (Índice por Módulo)

Este arquivo funciona como **índice** das rotas da área Tenant já detalhadas por módulo.

## Organização geral

- Prefixos principais:
  - `/customer/{slug}` → login, área pública de agendamento e formulários públicos.
  - `/workspace/{slug}` → área autenticada do tenant (rotas mapeadas pelos módulos abaixo).
- Guard principal: `tenant` (mais middlewares como `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`).

As definições completas vivem em `routes/tenant.php`. Os detalhes por módulo estão nos arquivos em `docs/20-tenant/modules/*/routes.md`.

## Rotas por módulo

- **Appointments**
  - Rotas autenticadas para CRUD de agendamentos internos (`/workspace/{slug}/appointments/*`) e APIs auxiliares (`/workspace/{slug}/api/doctors/...`).
  - Detalhamento em: `docs/20-tenant/modules/appointments/routes.md`.

- **Recurring Appointments**
  - Rotas dedicadas de recorrência (`/workspace/{slug}/agendamentos/recorrentes/*`) e APIs de horários disponíveis para séries recorrentes.
  - Detalhamento em: `docs/20-tenant/modules/recurring-appointments/routes.md`.

- **Forms**
  - Rotas para CRUD de formulários (`/workspace/{slug}/forms/*`), builder, preview e endpoints internos de seções/perguntas/opções.
  - Detalhamento em: `docs/20-tenant/modules/forms/routes.md`.

- **Form Responses**
  - Rotas para listar, responder e revisar respostas de formulários (`/workspace/{slug}/responses/*`) e grid-data.
  - Detalhamento em: `docs/20-tenant/modules/form-responses/routes.md`.

- **Business Hours**
  - Rotas para gerenciar horários de atendimento por médico e dia da semana (`/workspace/{slug}/business-hours/*`) e endpoint `grid-data` para listagem.
  - Detalhamento em: `docs/20-tenant/modules/business-hours/routes.md`.

- **Appointment Types**
  - Rotas para gerenciar tipos de atendimento por médico (`/workspace/{slug}/appointment-types/*`) e endpoint `grid-data` para listagem.
  - Detalhamento em: `docs/20-tenant/modules/appointment-types/routes.md`.

- **Calendar Sync**
  - Rotas para gerenciar estados de sincronização de calendário (`/workspace/{slug}/calendar-sync/*`).
  - Detalhamento em: `docs/20-tenant/modules/calendar-sync/routes.md`.

- **Online Appointments**
  - Rotas para gerenciar agendamentos online (`/workspace/{slug}/appointments/online/*`) e endpoint `grid-data`.
  - Detalhamento em: `docs/20-tenant/modules/online-appointments/routes.md`.

- **Notifications**
  - Rotas para listar, visualizar e marcar notificações como lidas (`/workspace/{slug}/notifications/*`) e endpoint JSON.
  - Detalhamento em: `docs/20-tenant/modules/notifications/routes.md`.

- **Campaigns**
  - Rotas autenticadas para CRUD e execução de campanhas (`/workspace/{slug}/campaigns/*`), incluindo:
    - `grid-data` (Grid.js server-side)
    - upload de assets (`/campaigns/assets`)
    - ações de disparo (`send-test`, `start`, `schedule`, `pause`, `resume`)
    - histórico (runs e recipients)
  - Detalhamento em: `docs/20-tenant/modules/campaigns/routes.md`.

- **Integrations**
  - Rotas para CRUD de integrações (`/workspace/{slug}/integrations/*`).
  - Detalhamento em: `docs/20-tenant/modules/integrations/routes.md`.

- **Public Customer**
  - Rotas públicas sob `/customer/{slug}` para identificação/cadastro de paciente, agendamento público e formulários públicos.
  - Detalhamento em: `docs/20-tenant/modules/public-customer/routes.md`.

- **Medical Appointments**
  - Rotas de sessão de atendimento médico do dia (`/workspace/{slug}/atendimento/*`).
  - Detalhamento em: `docs/20-tenant/modules/medical-appointments/routes.md`.

> Para a visão mais ampla de rotas do Tenant (incluindo portal do paciente e área pública), consulte também `TENANT.md` e `ARQUITETURA.md`.
