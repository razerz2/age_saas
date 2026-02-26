# Campanhas — Troubleshooting

Este guia lista causas comuns e ações objetivas de diagnóstico para o MVP atual.

Fontes: `EnsureCampaignModuleEnabled`, `CampaignChannelGate`, `CampaignAutomationRunner`, `CampaignDeliveryService`, jobs e views.

## Campanhas indisponíveis (sem canais)

Sintoma:

- UI mostra “Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.”
- Rotas com `campaign.module.enabled` redirecionam com `warning` ou respondem `403` em JSON.

Causa:

- `CampaignChannelGate::availableChannels()` retorna `[]` (tenant sem provedores configurados).

Solução:

- Configurar integrações do tenant (Email e/ou WhatsApp) para que `TenantSetting::emailProvider()` e/ou `TenantSetting::whatsappProvider()` estejam com `driver=tenancy` e settings obrigatórios.
- Referências:
  - Regras: `app/Services/Tenant/CampaignChannelGate.php`
  - Settings obrigatórios: `config/campaigns.php`

## Não aparece Email/WhatsApp no create/edit

Causa:

- O formulário usa `availableChannels` do `CampaignChannelGate`.
- Se o provider não está “completo”, o canal não é oferecido no form.

Solução:

- Revisar integrações do tenant e os required settings do canal em `config/campaigns.php`.

## Campanha automatizada não executa

Checklist (causa → solução):

- `type` não é `automated`:
  - Ajustar no form (select “Automatizada”) e salvar.
- `status` não é `active`:
  - Retomar campanha se estiver `paused` (`resume`).
- `automation_json` inválido:
  - Trigger deve ser `birthday` ou `inactive_patients`.
  - Schedule deve ser `daily` com `time` no formato `HH:MM`.
  - Timezone deve ser válida (fallback é `config('campaigns.automation.default_timezone')`).
  - Validação/parse: `app/Services/Tenant/CampaignAutomationRunner.php`.
- Fora da janela de horário:
  - O runner só executa dentro de uma tolerância de minutos (`config('campaigns.automation.window_tolerance_minutes')`).
- Lock do dia já existe:
  - `campaign_automation_locks` tem índice único por (`campaign_id`, `trigger`, `window_date`).
  - Se já houver lock (mesmo `done`), o runner pula a campanha no mesmo dia/trigger.
- Scheduler/cron não está rodando:
  - Verificar `app/Console/Kernel.php` (agenda `campaigns:run-automated` everyFiveMinutes).
  - Verificar execução do cron/queue no ambiente.

## Recipients ficam `pending`

Causas comuns:

- Fila não está rodando:
  - `CampaignStarter` dispara `ProcessCampaignRunJob` na fila `config('campaigns.queue')` (default `campaigns`).
  - Verificar workers/processadores de queue.
- Campanha foi pausada durante o processamento:
  - `ProcessCampaignRunJob` marca pending como `skipped` e finaliza o run.
  - Se o run ainda está `running`, verifique se o job está ativo.
- Canais ficaram indisponíveis:
  - O job valida canal com `CampaignChannelGate::assertChannelsEnabled()` antes de cada envio.
  - Se falhar, recipient vai para `error` e a auditoria registra o motivo.

## Mídia via upload falha no WhatsApp

Sintomas comuns:

- Erro indicando que envio de mídia é apenas WAHA (MVP).
- Erro indicando necessidade de URL pública do asset.

Causas (fatuais):

- `WhatsAppSender::sendMediaFromUrl()` lança erro quando o provider não é WAHA:
  - “Envio de mídia disponível apenas para o provedor WAHA neste MVP.”
- Para `media.source=upload`, o backend resolve `asset_id` e precisa de URL pública:
  - `CampaignDeliveryService::resolvePublicUrl()` só gera URL quando:
    - disk é `public`, ou
    - disk usa driver `s3` (usa `temporaryUrl`).
  - Assets de campanhas são salvos no disk `tenant_uploads` por padrão (sem URL pública no response do upload).

Soluções (MVP):

- Usar `media.source=url` e informar uma URL acessível publicamente.
- Se for necessário `upload`, garantir que o disk do asset suporte URL pública (public/s3) conforme implementação atual.

## Inatividade não encontra pacientes (`inactive_patients`)

Possíveis causas:

- Falha ao consultar appointments:
  - O builder depende de `appointments` e do campo `starts_at` para calcular `MAX(starts_at)`.
  - Se ocorrer exceção, o builder retorna lista vazia e loga `campaign_audience_inactive_patients_not_available`.
- Filtros removem todos:
  - Por padrão, filtra `patients.is_active=true`.
  - Se a campanha exige email/whatsapp (`audience_json.require.*`), pacientes sem contato são excluídos.

Onde olhar:

- `app/Services/Tenant/CampaignAudienceBuilder.php` (`applyInactivePatientsFilter` e flags `require.*`).

## Menu não mostra Campanhas

Causa:

- O menu só exibe Campanhas quando:
  - usuário é `admin`, ou
  - `user.modules` contém `campaigns`.

Solução:

- Ajustar o campo `modules` do usuário para incluir `campaigns` (ou usar um usuário admin).
- Referência: `resources/views/layouts/tailadmin/sidebar.blade.php` (`$hasCampaignsAccess`).

