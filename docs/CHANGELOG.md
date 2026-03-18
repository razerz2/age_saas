# CHANGELOG da Documentacao

2026-03-14 (WhatsApp)
- Consolidada a documentacao dos modulos de WhatsApp apos a separacao Oficial vs Nao Oficial:
  - `whatsapp-official-templates` (catalogo global + fluxo Meta)
  - `whatsapp-official-tenant-templates` (baseline oficial tenant: eventos clinicos + fluxo Meta)
  - `whatsapp-unofficial-templates` (catalogo interno nao oficial + teste manual/preview)
  - `tenant-default-notification-templates` (baseline nao oficial tenant + provisionamento)
- Oficial Tenant: documentados os fluxos de:
  - lookup por nome canonico remoto (fallback quando nome local diverge)
  - envio usando schema remoto aprovado (placeholders efetivos)
  - mapeamento semantico por `key/slot` para evitar troca de significado nos placeholders
- Troubleshooting: documentados erros Meta `132001` e `132000` e o caso de envio com conteudo trocado (mapeamento semantico).

2026-03-14
- Novo modulo Platform `tenant-default-notification-templates` para baseline operacional de Tenant (chaves `appointment.*` e `waitlist.*`) em tabela separada `tenant_default_notification_templates`.
- Provisionamento de tenant atualizado para copiar baseline ativo para `tenant.notification_templates` com idempotencia.
- Criado comando administrativo de compatibilidade para tenants existentes:
  - `php artisan tenants:seed-default-notification-templates` (dry-run)
  - `php artisan tenants:seed-default-notification-templates --tenant=<slug|uuid> --apply`
  - `php artisan tenants:seed-default-notification-templates --all-tenants --apply [--overwrite]`
- Platform `whatsapp-official-templates`: documentado baseline SaaS (Platform) e baseline clinico (Tenant) no catalogo global, com navegacao separada.
- Documentado boundary de dominio: templates operacionais de clinica permanecem no Tenant (`config/notification_templates.php` + `notification_templates` tenant).
- Adicionada orientacao de limpeza legada via comando administrativo:
  - `php artisan whatsapp-official-templates:clean-clinical` (dry-run)
  - `php artisan whatsapp-official-templates:clean-clinical --apply --mode=archive|delete`
- Platform `whatsapp-official-templates`: criado baseline SaaS com 8 eventos reais (`invoice.*`, `tenant.*`, `security.*`, `subscription.*`, `credentials.*`).
- Padronizacao de variaveis no baseline: `customer_name`, `tenant_name`, `invoice_amount`, `due_date`, `payment_link`, `code`.
- Frontend do modulo atualizado com descricoes curtas por evento SaaS e suporte de categoria `SECURITY` no formulario.
- Integracao Meta atualizada para mapear categoria interna `SECURITY` para `AUTHENTICATION`.
- Platform `whatsapp-official-templates`: adicionado teste manual de envio na UI (Show) via modal + endpoint `test-send`.
- Teste manual: geracao de payload baseada no schema remoto aprovado salvo em `meta_response` (inclui parametros dinamicos de `BUTTONS` quando aplicavel).
- Melhorado diagnostico de erros da Meta e logs estruturados para suporte operacional do teste manual.

2026-03-12
- Modulo Platform `whatsapp-official-templates`: suporte a `sample_variables` (exemplos obrigatorios de variaveis) para criacao/submissao na Meta.
- Ajustado payload da Graph API v22.0 para incluir `BODY.example.body_text` quando ha placeholders no `body_text`.
- Validacao: bloqueia envio quando placeholders nao possuem exemplos em `sample_variables`.
- Migration: adicionado campo `sample_variables` (JSON) em `whatsapp_official_templates`.
- UI: create/edit/show com campo e exibicao de exemplos.
- Correcao de encoding/mojibake no modulo (views e mensagens).


2026-02-22
- Criada estrutura base de documentação em `/docs` organizada por áreas.
- Adicionado `docs/README.md` como índice principal da nova estrutura.
- Preservado o índice antigo em `docs/_legacy/README_legacy.md`.
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

