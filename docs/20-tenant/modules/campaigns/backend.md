# Campanhas — Backend

Fontes principais:

- `routes/tenant.php`
- Controllers em `app/Http/Controllers/Tenant/*Campaign*.php`
- Services em `app/Services/Tenant/Campaign*.php`
- Jobs em `app/Jobs/Tenant/*Campaign*.php`
- Command em `app/Console/Commands/RunAutomatedCampaigns.php`

## Controllers (responsabilidades)

### `App\Http\Controllers\Tenant\CampaignController`

- `index()`:
  - Resolve `availableChannels` via `CampaignChannelGate`.
  - Injeta flags para UI (`moduleEnabled`, `moduleWarning`) e renderiza `tenant.campaigns.index`.
- `gridData()`:
  - Endpoint server-side para Grid.js (search/sort/paginação).
  - Renderiza células HTML via partials `tenant.campaigns.partials.status_badge` e `tenant.campaigns.partials.actions`.
- `create()` / `edit()`:
  - Renderiza form com canais disponíveis.
- `store()` / `update()`:
  - Persiste `channels_json`, `content_json`, `audience_json`, `automation_json`, `rules_json`, `schedule_*`, `scheduled_at`.
  - `automation_json` é `null` quando `type=manual`.
  - `store()` define campanhas `type=automated` como `status=active` por padrão (manual permanece `draft`).
- `show()`:
  - Calcula `unavailableChannels` (diferença entre canais da campanha e canais disponíveis).
  - Para campanhas automatizadas:
    - Carrega último `CampaignRun` (ordenado por `started_at`/`id`).
    - Resolve “próxima execução prevista” via `resolveNextAutomationRun()`.

Requests usados:

- `app/Http/Requests/Tenant/StoreCampaignRequest.php`
- `app/Http/Requests/Tenant/UpdateCampaignRequest.php` (herda `StoreCampaignRequest`)

### `App\Http\Controllers\Tenant\CampaignDispatchController`

Disparo manual:

- `sendTest()`:
  - Valida destino e canal (exige seleção de canal quando há mais de um).
  - Usa `CampaignChannelGate::assertChannelsEnabled()` para bloquear canal indisponível.
  - Usa `CampaignDeliveryService::sendTest()` para enviar.
- `start()`:
  - Garante canais habilitados.
  - Se status estava `draft`, move para `active`.
  - Zera `scheduled_at`.
  - Inicia `CampaignStarter::startCampaign(..., trigger='manual')` e redireciona para Runs.
- `schedule()`:
  - Valida `scheduled_at` (presente/futuro).
  - Garante canais habilitados.
  - Persiste `scheduled_at` e garante status `active` se era `draft`.
  - Dispara `StartCampaignJob` com `delay($scheduledAt)` e fila `config('campaigns.queue')`.
- `pause()` / `resume()`:
  - Atualiza `campaign.status` para `paused`/`active`.

### `App\Http\Controllers\Tenant\CampaignRunController`

- `index()` renderiza `tenant.campaigns.runs.index`.
- `gridData()` entrega JSON para Grid.js (`runs/grid-data`), incluindo:
  - `status_badge` e `actions` via partials.
  - `totals` a partir de `campaign_runs.totals_json`.

### `App\Http\Controllers\Tenant\CampaignRecipientController`

- `index()` renderiza `tenant.campaigns.recipients.index`, com filtro opcional `run_id`.
- `gridData()` entrega JSON para Grid.js (`recipients/grid-data`), incluindo:
  - `status_badge` e `actions` via partials.
  - Coluna `error_message` com limite (via `Str::limit`).

### `App\Http\Controllers\Tenant\CampaignAssetController`

- `store()`:
  - Upload de arquivo (max 20MB) e validação por `kind`:
    - `email_attachment`
    - `whatsapp_image`, `whatsapp_video`, `whatsapp_document`, `whatsapp_audio`
  - Salva em `Storage` no disk `tenant_uploads` e registra em `assets`.
  - Response inclui `asset_id` e metadados (não retorna URL pública).

## Services (regras e execução)

### `App\Services\Tenant\CampaignChannelGate`

- Define canais disponíveis por tenant em runtime:
  - Email disponível apenas se `TenantSetting::emailProvider()` tiver `driver=tenancy` e settings obrigatórios (`config/campaigns.php`).
  - WhatsApp disponível apenas se `TenantSetting::whatsappProvider()` tiver `driver=tenancy` e settings obrigatórios (por provider ou legacy).
- `assertChannelsEnabled($channels)`:
  - Lança `DomainException` quando não há canais ou quando um canal solicitado é indisponível.

### `App\Services\Tenant\CampaignStarter`

Responsável por iniciar uma execução (Run) e gerar Recipients.

- Evita concorrência:
  - Dentro de transação (`tenant`), procura `CampaignRun` `running` com `lockForUpdate()`.
  - Se existir, não cria outro run.
- Cria `CampaignRun` com:
  - `status=running`, `started_at=now()`
  - `context_json` com `trigger`, `channels`, `audience_snapshot`, `initiated_by`, `initiated_at`.
  - `totals_json` inicial.
- Gera `CampaignRecipient` (um por alvo + canal) com `insertOrIgnore` em chunks e garante unicidade por (`run_id`, `channel`, `destination`).
- Dispara `ProcessCampaignRunJob` quando há pendências (`pending > 0`), na fila `config('campaigns.queue')`.

### `App\Services\Tenant\CampaignAudienceBuilder`

Gera audiência (MVP) com `source=patients`:

- Trigger `birthday`:
  - Filtra por mês/dia de nascimento do paciente no timezone resolvido.
- Trigger `inactive_patients`:
  - Usa subquery `MAX(appointments.starts_at)` por `patient_id`.
  - Considera inativo quando `last_appointment_at` é `NULL` ou menor que `now - inactivity_days`.
  - Exclui appointments com status `canceled`/`cancelled`.
- Filtros e flags:
  - `patients.is_active` é `true` por padrão (pode ser controlado via `audience_json.filters.patient.is_active`).
  - `audience_json.require.email` e `audience_json.require.whatsapp` controlam exclusões por ausência de contato.

Regras opcionais (`rules_json`) para campanhas agendadas:

- Aplicadas com whitelist de campos/operadores em `App\Support\Tenant\CampaignPatientRules`.
- Campos MVP: `gender`, `is_active`, `birth_date`, `city`, `state`.
- Operadores MVP: `=`, `!=`, `in`, `not_in`, `is_null`, `is_not_null`, `birthday_today`.
- `birthday_today` tenta `CONVERT_TZ` e faz fallback para comparação por `Carbon::now(tenant_tz)` quando necessário.

### `App\Services\Tenant\CampaignAutomationRunner`

Avalia e inicia campanhas automatizadas:

- Seleciona campanhas `type=automated` e `status=active`.
- Resolve programação a partir dos campos `schedule_*` (fonte de verdade):
  - `schedule_mode` (`period|indefinite`)
  - `starts_at` obrigatório
  - `ends_at` obrigatório quando `period`
  - `schedule_weekdays` (fallback para todos os dias quando legado vazio)
  - `schedule_times` (`HH:MM`, com fallback legado para `automation_json.schedule.time` quando existir)
  - `timezone` da campanha (fallback para timezone do tenant)
- Elegibilidade por minuto (timezone local do tenant/campanha):
  - `starts_at <= now`
  - em `period`, `now <= ends_at`
  - `dayOfWeek(now)` pertence a `schedule_weekdays`
  - `now->format('H:i')` pertence a `schedule_times`
- Idempotência:
  - Cria lock em `campaign_automation_locks` com `window_key` (`YYYY-MM-DD HH:MM`).
  - Unique por (`campaign_id`, `window_key`) evita disparo duplicado no mesmo minuto.
  - Tratamento de race-condition por captura de unique violation (`QueryException`).
- Logs estruturados:
  - Eventos de skip/start com `tenant_id`, `campaign_id`, `window_key`, `eligibility` e `reason`.
  - Eventos de erro com contexto completo para troubleshooting.

### Render e envio

- `App\Services\Tenant\CampaignRenderer`:
  - Renderiza `content_json` por canal (`email`/`whatsapp`).
  - Usa `TemplateRenderer` para placeholders e remove tokens `{{...}}` não resolvidos.
- `App\Services\Tenant\CampaignDeliveryService`:
  - Envia por `EmailSender` (email) e `WhatsAppSender` (whatsapp).
  - WhatsApp:
    - `message_type=text` usa `WhatsAppSender::send()`.
    - `message_type=media` com `source=url` usa `sendMediaFromUrl(...)`.
    - `message_type=media` com `source=upload` resolve `Asset` por `asset_id` e exige URL pública:
      - `resolvePublicUrl()` só resolve URL para disk `public` ou disks `s3` (via `temporaryUrl`).
      - Se não houver URL pública, falha com erro explícito.
  - Auditoria de entrega é registrada via `NotificationDeliveryLogger` (tabela `notification_deliveries`).

## Jobs / filas

### `App\Jobs\Tenant\StartCampaignJob`

- Executa campanha agendada:
  - Faz `tenant->makeCurrent()`.
  - Revalida se `scheduled_at` ainda está no futuro; se sim, re-releasa o job.
  - Chama `CampaignStarter::startCampaign(..., trigger='scheduled')`.

### `App\Jobs\Tenant\ProcessCampaignRunJob`

- Processa recipients `pending` do run:
  - Revalida `run.status == running`.
  - Se campanha está `paused`:
    - Marca `pending` como `skipped` com mensagem.
    - Atualiza totais e finaliza run.
  - Chunk por 100, com rate limit por canal (`config/campaigns.php`).
  - Para cada recipient:
    - `CampaignChannelGate::assertChannelsEnabled([channel])` (se falhar, marca `error` e loga em `notification_deliveries`).
    - `CampaignDeliveryService::sendRecipient()` envia e marca `sent` ou `error`.
  - Recalcula `totals_json` e seta status final do run.

## Middleware e bloqueios

- `App\Http\Middleware\Tenant\EnsureCampaignModuleEnabled` (alias `campaign.module.enabled` em `app/Http/Kernel.php`):
  - Bloqueia ações quando `CampaignChannelGate::availableChannels()` retorna `[]`.
  - Para HTML, redireciona e grava flash `warning` com mensagem.
  - Para JSON, responde `403` com `{message: ...}`.

## Multi-tenant (Spatie multitenancy)

- `campaigns:run-automated` (`app/Console/Commands/RunAutomatedCampaigns.php`):
  - Itera tenants `status=active`, faz `makeCurrent()` e roda automações.
  - `--tenant=*` filtra por id/subdomain.
  - `--dry-run` apenas contabiliza elegibilidade, não cria lock/run.
- Scheduler:
  - `app/Console/Kernel.php` agenda `campaigns:run-automated` `everyMinute()->withoutOverlapping()`.
