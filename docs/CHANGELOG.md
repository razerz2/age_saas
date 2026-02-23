# 📝 CHANGELOG da Documentação

2026-02-22
- Criada estrutura base de documentação em `/docs` organizada por áreas.
- Adicionado `docs/README.md` como índice principal da nova estrutura.
- Preservado o índice antigo em `docs/README_legacy.md`.
- Criada pasta `00-global/` com documento de visão geral.
- Reservadas pastas para as áreas: `10-platform/`, `20-tenant/`, `30-landing-page/`, `40-portal-paciente/`.
- Adicionados templates iniciais em `docs/_templates/` para áreas e módulos.

2026-02-22
- Finalização da Etapa 1.
- Criados blocos completos de documentação para `20-tenant`, `30-landing-page` e `40-portal-paciente` (READMEs, índices e `modules/.gitkeep`).
- Padronizados todos os READMEs de área com seções: Objetivo, Arquivos desta área, Módulos, Referências globais.
- Criada governança de rascunhos em `docs/_drafts/README.md`.
- Atualizado `docs/README.md` com seção "Como contribuir" e seção "Rascunhos".
- Incluídos banners de reorganização no topo de `ARQUITETURA.md`, `PLATFORM.md` e `TENANT.md` apontando para `docs/README.md`.

2026-02-22
- Etapa 3.1 (Tenant) — índices e links.
- Transformados `docs/20-tenant/02-rotas.md`, `03-estrutura-de-pastas.md` e `04-padroes-ui-tenant.md` em índices que apontam para os módulos pilotados (appointments, recurring-appointments, forms, form-responses).
- Adicionados links cruzados para os arquivos de módulo (`routes.md`, `views.md`, `frontend.md`, `database.md`) sem duplicar listas completas de rotas ou views.

2026-02-22
- Etapa 4A.1 (Tenant) — business-hours.
- Documentado o módulo `business-hours` em `docs/20-tenant/modules/business-hours/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados índices de Tenant para incluir o módulo `business-hours` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4A.2 (Tenant) — appointment-types.
- Documentado o módulo `appointment-types` em `docs/20-tenant/modules/appointment-types/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados índices de Tenant para incluir o módulo `appointment-types` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4A.3 (Tenant) — calendar-sync.
- Documentado o módulo `calendar-sync` em `docs/20-tenant/modules/calendar-sync/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados índices de Tenant para incluir o módulo `calendar-sync` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4B.1 (Tenant) — online-appointments + notifications.
- Documentado o módulo `online-appointments` em `docs/20-tenant/modules/online-appointments/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Documentado o módulo `notifications` em `docs/20-tenant/modules/notifications/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados índices de Tenant para incluir `online-appointments` e `notifications` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4B.2 (Tenant) — integrations + public-customer.
- Documentado o módulo `integrations` em `docs/20-tenant/modules/integrations/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Documentado o módulo `public-customer` em `docs/20-tenant/modules/public-customer/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados índices de Tenant para incluir `integrations` e `public-customer` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4B.3 (Tenant) — medical-appointments.
- Documentado o módulo `medical-appointments` em `docs/20-tenant/modules/medical-appointments/` com os 9 arquivos padrão (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados índices de Tenant para incluir `medical-appointments` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).
