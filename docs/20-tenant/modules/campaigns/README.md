# Módulo: Campanhas

Mapa rápido do módulo de **Campanhas** do Tenant.

## Arquivos deste módulo

- `overview.md` — conceito, regras e glossário.
- `routes.md` — rotas do módulo (CRUD + disparos + histórico + assets).
- `views.md` — telas e partials Blade do módulo.
- `backend.md` — controllers, services, jobs, middleware e automações.
- `frontend.md` — Grid.js server-side e comportamento do form (uploads/inputs).
- `database.md` — tabelas, índices e schemas JSON versionados.
- `permissions.md` — regras de acesso (módulos do usuário + middleware + gates).
- `troubleshooting.md` — problemas comuns e checklist objetivo.

## O que o módulo faz hoje (MVP)

- CRUD de campanhas (listar via Grid.js, criar, editar, visualizar e excluir).
- Execução manual:
  - Enviar teste (`send-test`).
  - Iniciar envio imediato (`start`).
  - Agendar envio (`schedule`) com `StartCampaignJob`.
  - Pausar/retomar (`pause`/`resume`).
- Execução automatizada (tipo `automated`):
  - Runner de automação diário por trigger (`birthday`, `inactive_patients`).
  - Idempotência diária por lock (`campaign_automation_locks`).
  - UI mostra última execução e próxima execução prevista.
- Gating de canais por integrações do tenant:
  - Opções de canal dinâmicas no formulário conforme integrações configuradas.
  - Middleware bloqueia ações quando não há canais disponíveis.
- Upload de assets (anexos de email e mídia WhatsApp) e referência por `asset_id` em `content_json`.
- Processamento em filas:
  - `ProcessCampaignRunJob` processa recipients pendentes com rate limit por canal.
- Auditoria de entregas:
  - Logs em `notification_deliveries` via `NotificationDeliveryLogger`.
- Histórico:
  - Runs e Recipients com Grid.js (server-side) por campanha.

## O que o módulo ainda não faz (escopo fora do MVP atual)

- Editor completo de audiência (além de “Pacientes ativos” com flags automáticas de exigir email/whatsapp).
- Triggers além de `birthday` e `inactive_patients`.
- Execução “exatamente no segundo”: automações rodam por janela (tolerância em minutos) e dependem do scheduler/cron.
- Envio de mídia por WhatsApp via upload em provedores diferentes de WAHA (no MVP, mídia é suportada apenas no WAHA).
- Download/gerenciamento de assets na UI (o endpoint registra e retorna `asset_id`, sem `url` pública no response).
- Relatórios avançados por campanha (dashboards, funis, conversões etc.).

## Fontes consultadas (paths)

- Rotas: `routes/tenant.php` (bloco CAMPAIGNS).
- Controllers:
  - `app/Http/Controllers/Tenant/CampaignController.php`
  - `app/Http/Controllers/Tenant/CampaignDispatchController.php`
  - `app/Http/Controllers/Tenant/CampaignRunController.php`
  - `app/Http/Controllers/Tenant/CampaignRecipientController.php`
  - `app/Http/Controllers/Tenant/CampaignAssetController.php`
- Requests: `app/Http/Requests/Tenant/StoreCampaignRequest.php`, `app/Http/Requests/Tenant/UpdateCampaignRequest.php`
- Services:
  - `app/Services/Tenant/CampaignChannelGate.php`
  - `app/Services/Tenant/CampaignStarter.php`
  - `app/Services/Tenant/CampaignAudienceBuilder.php`
  - `app/Services/Tenant/CampaignAutomationRunner.php`
  - `app/Services/Tenant/CampaignRenderer.php`
  - `app/Services/Tenant/CampaignDeliveryService.php`
  - `app/Services/Tenant/EmailSender.php`, `app/Services/Tenant/WhatsAppSender.php`
  - `app/Services/Tenant/NotificationDeliveryLogger.php`
- Jobs: `app/Jobs/Tenant/StartCampaignJob.php`, `app/Jobs/Tenant/ProcessCampaignRunJob.php`
- Middleware: `app/Http/Middleware/Tenant/EnsureCampaignModuleEnabled.php`, `app/Http/Kernel.php` (alias `campaign.module.enabled`)
- Automação (command + scheduler): `app/Console/Commands/RunAutomatedCampaigns.php`, `app/Console/Kernel.php`
- Views:
  - `resources/views/tenant/campaigns/*.blade.php`
  - `resources/views/tenant/campaigns/partials/*.blade.php`
  - `resources/views/tenant/campaigns/runs/*`
  - `resources/views/tenant/campaigns/recipients/*`
- Frontend (JS): `resources/js/tenant/pages/campaigns.js`
- Database (migrations):
  - `database/migrations/tenant/2026_02_25_000007_create_campaigns_table.php`
  - `database/migrations/tenant/2026_02_25_000008_create_campaign_runs_table.php`
  - `database/migrations/tenant/2026_02_25_000009_create_campaign_recipients_table.php`
  - `database/migrations/tenant/2026_02_25_000010_create_assets_table.php`
  - `database/migrations/tenant/2026_02_25_000011_create_campaign_automation_locks_table.php`
  - `database/migrations/tenant/2026_02_25_000006_create_notification_deliveries_table.php`
- Config: `config/campaigns.php`

