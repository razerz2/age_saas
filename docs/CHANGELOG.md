# ðŸ“ CHANGELOG da DocumentaÃ§Ã£o

2026-02-22
- Criada estrutura base de documentaÃ§Ã£o em `/docs` organizada por Ã¡reas.
- Adicionado `docs/README.md` como Ã­ndice principal da nova estrutura.
- Preservado o Ã­ndice antigo em `docs/_legacy/README_legacy.md`.
- Criada pasta `00-global/` com documento de visÃ£o geral.
- Reservadas pastas para as Ã¡reas: `10-platform/`, `20-tenant/`, `30-landing-page/`, `40-portal-paciente/`.
- Adicionados templates iniciais em `docs/_templates/` para Ã¡reas e mÃ³dulos.

2026-02-22
- FinalizaÃ§Ã£o da Etapa 1.
- Criados blocos completos de documentaÃ§Ã£o para `20-tenant`, `30-landing-page` e `40-portal-paciente` (READMEs, Ã­ndices e `modules/.gitkeep`).
- Padronizados todos os READMEs de Ã¡rea com seÃ§Ãµes: Objetivo, Arquivos desta Ã¡rea, MÃ³dulos, ReferÃªncias globais.
- Criada governanÃ§a de rascunhos em `docs/_drafts/README.md`.
- Atualizado `docs/README.md` com seÃ§Ã£o "Como contribuir" e seÃ§Ã£o "Rascunhos".
- IncluÃ­dos banners de reorganizaÃ§Ã£o no topo de `ARQUITETURA.md`, `PLATFORM.md` e `TENANT.md` apontando para `docs/README.md`.

2026-02-22
- Etapa 3.1 (Tenant) â€” Ã­ndices e links.
- Transformados `docs/20-tenant/02-rotas.md`, `03-estrutura-de-pastas.md` e `04-padroes-ui-tenant.md` em Ã­ndices que apontam para os mÃ³dulos pilotados (appointments, recurring-appointments, forms, form-responses).
- Adicionados links cruzados para os arquivos de mÃ³dulo (`routes.md`, `views.md`, `frontend.md`, `database.md`) sem duplicar listas completas de rotas ou views.

2026-02-22
- Etapa 4A.1 (Tenant) â€” business-hours.
- Documentado o mÃ³dulo `business-hours` em `docs/20-tenant/modules/business-hours/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados Ã­ndices de Tenant para incluir o mÃ³dulo `business-hours` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4A.2 (Tenant) â€” appointment-types.
- Documentado o mÃ³dulo `appointment-types` em `docs/20-tenant/modules/appointment-types/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados Ã­ndices de Tenant para incluir o mÃ³dulo `appointment-types` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4A.3 (Tenant) â€” calendar-sync.
- Documentado o mÃ³dulo `calendar-sync` em `docs/20-tenant/modules/calendar-sync/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados Ã­ndices de Tenant para incluir o mÃ³dulo `calendar-sync` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4B.1 (Tenant) â€” online-appointments + notifications.
- Documentado o mÃ³dulo `online-appointments` em `docs/20-tenant/modules/online-appointments/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Documentado o mÃ³dulo `notifications` em `docs/20-tenant/modules/notifications/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados Ã­ndices de Tenant para incluir `online-appointments` e `notifications` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4B.2 (Tenant) â€” integrations + public-customer.
- Documentado o mÃ³dulo `integrations` em `docs/20-tenant/modules/integrations/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Documentado o mÃ³dulo `public-customer` em `docs/20-tenant/modules/public-customer/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados Ã­ndices de Tenant para incluir `integrations` e `public-customer` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).

2026-02-22
- Etapa 4B.3 (Tenant) â€” medical-appointments.
- Documentado o mÃ³dulo `medical-appointments` em `docs/20-tenant/modules/medical-appointments/` com os 9 arquivos padrÃ£o (README, overview, routes, views, backend, frontend, database, permissions, troubleshooting).
- Atualizados Ã­ndices de Tenant para incluir `medical-appointments` (`docs/20-tenant/README.md` e `docs/20-tenant/02-rotas.md`).


2026-02-26
- Docs cleanup (varredura/higienizacao da raiz de `docs/`).
- Raiz de `docs/` reduzida para: `README.md` e `CHANGELOG.md`.

Movidos (origem -> destino)
- `docs/CONTROLE_FEATURES_PLANO.md` -> `docs/10-platform/guides/CONTROLE_FEATURES_PLANO.md`
- `docs/ENV.md` -> `docs/10-platform/guides/ENV.md`
- `docs/CONFIGURACAO_EMAIL.md` -> `docs/10-platform/integrations/CONFIGURACAO_EMAIL.md`
- `docs/CONFIGURACAO_TITAN_EMAIL.md` -> `docs/10-platform/integrations/CONFIGURACAO_TITAN_EMAIL.md`
- `docs/HARDENING_PRODUCAO.md` -> `docs/10-platform/operations/HARDENING_PRODUCAO.md`
- `docs/APPOINTMENT_FINANCE_OBSERVER.md` -> `docs/20-tenant/finance/APPOINTMENT_FINANCE_OBSERVER.md`
- `docs/AUDITORIA_REFATORACAO_FINANCEIRO.md` -> `docs/20-tenant/finance/AUDITORIA_REFATORACAO_FINANCEIRO.md`
- `docs/CONCILIACAO_FINANCEIRA.md` -> `docs/20-tenant/finance/CONCILIACAO_FINANCEIRA.md`
- `docs/FINANCE_GO_LIVE_CHECKLIST.md` -> `docs/20-tenant/finance/FINANCE_GO_LIVE_CHECKLIST.md`
- `docs/FLUXO_COBRANCA_COMPLETO.md` -> `docs/20-tenant/finance/FLUXO_COBRANCA_COMPLETO.md`
- `docs/MODULO_FINANCEIRO_COMPLETO.md` -> `docs/20-tenant/finance/MODULO_FINANCEIRO_COMPLETO.md`
- `docs/MODULO_FINANCEIRO_TENANT.md` -> `docs/20-tenant/finance/MODULO_FINANCEIRO_TENANT.md`
- `docs/RELATORIOS_FINANCEIROS.md` -> `docs/20-tenant/finance/RELATORIOS_FINANCEIROS.md`
- `docs/RESUMO_MODULO_FINANCEIRO.md` -> `docs/20-tenant/finance/RESUMO_MODULO_FINANCEIRO.md`
- `docs/FLUXO_NOTIFICACOES_TENANT.md` -> `docs/20-tenant/guides/FLUXO_NOTIFICACOES_TENANT.md`
- `docs/FRONTEND_TENANT.md` -> `docs/20-tenant/guides/FRONTEND_TENANT.md`
- `docs/GUIA_CRIAR_FORMULARIO.md` -> `docs/20-tenant/guides/GUIA_CRIAR_FORMULARIO.md`
- `docs/TIPOS_CONSULTA_VIEWS.md` -> `docs/20-tenant/guides/TIPOS_CONSULTA_VIEWS.md`
- `docs/tenant-grid-pattern.md` -> `docs/20-tenant/guides/tenant-grid-pattern.md`
- `docs/INTEGRACAO_APPLE_CALENDAR.md` -> `docs/20-tenant/integrations/INTEGRACAO_APPLE_CALENDAR.md`
- `docs/INTEGRACAO_GOOGLE_CALENDAR.md` -> `docs/20-tenant/integrations/INTEGRACAO_GOOGLE_CALENDAR.md`
- `docs/SISTEMA_PAGAMENTOS_ASAAS.md` -> `docs/20-tenant/integrations/SISTEMA_PAGAMENTOS_ASAAS.md`
- `docs/INSTRUCOES_MIGRATION.md` -> `docs/20-tenant/operations/INSTRUCOES_MIGRATION.md`
- `docs/MIGRATIONS_CLEANUP.md` -> `docs/20-tenant/operations/MIGRATIONS_CLEANUP.md`
- `docs/CORRECAO_APP_URL.md` -> `docs/20-tenant/troubleshooting/CORRECAO_APP_URL.md`
- `docs/DIAGNOSTICO_GOOGLE_CALLBACK.md` -> `docs/20-tenant/troubleshooting/DIAGNOSTICO_GOOGLE_CALLBACK.md`
- `docs/GUIA_TESTE_SESSAO_EXPIRADA.md` -> `docs/20-tenant/troubleshooting/GUIA_TESTE_SESSAO_EXPIRADA.md`
- `docs/SOLUCAO_ERRO_419.md` -> `docs/20-tenant/troubleshooting/SOLUCAO_ERRO_419.md`
- `docs/GUIA_TESTE_PUBLICO.md` -> `docs/30-landing-page/guides/GUIA_TESTE_PUBLICO.md`
- `docs/README_legacy.md` -> `docs/_legacy/README_legacy.md`
- `docs/MODULO_FINANCEIRO.md` -> `docs/_legacy/MODULO_FINANCEIRO.md`

Mesclados (A + B -> canonico)
- `RESUMO_MODULO_FINANCEIRO.md` + `MODULO_FINANCEIRO_COMPLETO.md` + `MODULO_FINANCEIRO.md` -> `docs/20-tenant/finance/MODULO_FINANCEIRO_TENANT.md` e `docs/20-tenant/finance/README.md` (arquivos legados mantidos como stubs).

Deprecados
- `docs/_legacy/MODULO_FINANCEIRO.md` (stub legado)
- `docs/_legacy/README_legacy.md` (indice historico)

Excluidos
- Nenhum arquivo removido nesta limpeza.

Novas estruturas e indices
- Criadas estruturas tematicas: `guides/`, `integrations/`, `finance/`, `troubleshooting/`, `operations/` nos dominios necessarios.
- Atualizados: `docs/README.md`, `docs/10-platform/README.md`, `docs/20-tenant/README.md`, `docs/30-landing-page/README.md`, `docs/40-portal-paciente/README.md`, `docs/00-global/05-integracoes.md`, `docs/00-global/06-deploy-e-operacao.md`, `docs/00-global/07-troubleshooting.md`.
- Criada documentacao por modulos (9 arquivos) para `10-platform/modules`, `30-landing-page/modules`, `40-portal-paciente/modules`.

