# Campanhas — Overview

Este módulo permite criar e executar **campanhas** de comunicação para o Tenant, com suporte a:

- Campanhas **manuais** (operadas pela UI: teste, iniciar, agendar, pausar/retomar).
- Campanhas **automatizadas** (executadas via scheduler, com idempotência diária por lock).

Fontes: `CampaignController`, `CampaignDispatchController`, `CampaignAutomationRunner`, `routes/tenant.php`.

## Conceitos

### Campanhas manuais

- Tipo: `manual`.
- Execução é iniciada por ação do usuário:
  - `send-test` envia um teste para um destino único, sem criar `Run`/`Recipient`.
  - `start` cria um `Run` (status `running`) e gera `Recipients` (status `pending`).
  - `schedule` agenda um `StartCampaignJob` para iniciar o `Run` no horário.

### Campanhas automatizadas

- Tipo: `automated`.
- A execução é avaliada periodicamente pelo comando `campaigns:run-automated` (scheduler).
- Só são elegíveis campanhas `type=automated` e `status=active`.
- A elegibilidade considera:
  - Programação `schedule_mode`, `starts_at`, `ends_at`, `schedule_weekdays`, `schedule_times`, `timezone`.
  - Janela exata por minuto (`HH:MM`) no timezone da campanha/tenant.
  - Canais disponíveis no tenant (integrações).
  - Lock por minuto em `campaign_automation_locks` por (`campaign_id`, `window_key`).

## Canais (Email / WhatsApp)

- Canais suportados hoje: `email` e `whatsapp` (config em `config/campaigns.php`).
- As opções no formulário são **dinâmicas** conforme integrações do tenant:
  - `CampaignChannelGate::availableChannels()` retorna `[]` quando o tenant não tem provedor configurado.
  - Se apenas um canal estiver disponível, o form fixa esse canal.
- Regra de negócio:
  - É possível ter `email`, `whatsapp` ou ambos, desde que habilitados por integração.
  - Se a campanha foi criada com um canal que depois ficou indisponível, a UI sinaliza e desabilita ações de envio.

## Regras de execução e histórico

- Uma execução é representada por um `CampaignRun` (run).
- Cada destinatário por canal é um `CampaignRecipient` (recipient).
- Processamento ocorre por fila (jobs) e atualiza totais no `run.totals_json`.
- Auditoria de envio:
  - Email e WhatsApp logam em `notification_deliveries` via `NotificationDeliveryLogger`.
- Idempotência:
  - Automações usam lock por minuto (`campaign_automation_locks`) para evitar dupla execução na mesma janela.
  - Recipients têm índice único por (`campaign_run_id`, `channel`, `destination`).
  - `CampaignStarter` impede duas execuções simultâneas criando apenas um `run` `running` por campanha (lock transacional).

## Glossário (MVP)

- **Campaign**: entidade principal (`campaigns`) com tipo, status, canais e JSONs versionados.
- **Run**: uma execução de envio (`campaign_runs`) com contexto e totais.
- **Recipient**: um destinatário por run/canal (`campaign_recipients`) com status e variáveis.
- **Asset**: arquivo enviado via endpoint de upload (`assets`) e referenciado por `asset_id` no `content_json`.
- **Automation Lock**: lock por minuto (`campaign_automation_locks`) que garante idempotência.
