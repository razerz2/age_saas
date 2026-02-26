# Campanhas — Database

Fontes: migrations em `database/migrations/tenant/*campaign*` e models em `app/Models/Tenant/*`.

## Tabelas (MVP)

### `campaigns`

Migration: `database/migrations/tenant/2026_02_25_000007_create_campaigns_table.php`

- `id`
- `name` (150)
- `type` (`manual|automated`)
- `status` (`draft|active|paused|archived|blocked`)
- `channels_json` (json array, ex.: `["email","whatsapp"]`)
- `content_json` (json versionado, `version=1`)
- `audience_json` (json versionado, `version=1`)
- `automation_json` (json versionado, `version=1`, nullable)
- `scheduled_at` (nullable) — usado no agendamento manual (`schedule`) e pelo `StartCampaignJob`
- `created_by` (nullable) — `user_id` do tenant
- `created_at`, `updated_at`

Model: `app/Models/Tenant/Campaign.php`

### `campaign_runs`

Migration: `database/migrations/tenant/2026_02_25_000008_create_campaign_runs_table.php`

- `id`
- `campaign_id` (FK → `campaigns`)
- `status` (`running|finished|error`)
- `started_at`, `finished_at`
- `context_json` (snapshot/context do run)
- `totals_json` (ex.: `{total, success, error, skipped, pending}`)
- `error_message` (500, nullable)
- `created_at`, `updated_at`

Model: `app/Models/Tenant/CampaignRun.php`

### `campaign_recipients`

Migration: `database/migrations/tenant/2026_02_25_000009_create_campaign_recipients_table.php`

- `id`
- `campaign_id` (FK → `campaigns`)
- `campaign_run_id` (FK → `campaign_runs`)
- `target_type` (ex.: `patient`)
- `target_id` (nullable)
- `channel` (`email|whatsapp`)
- `destination` (255) — email ou telefone (texto; UI sugere E.164)
- `status` (`pending|sent|error|skipped`)
- `sent_at` (nullable)
- `error_message` (500, nullable)
- `vars_json` (nullable) — snapshot de variáveis do destinatário (cast array no model)
- `meta_json` (nullable) — reservado para metadados de entrega (cast array no model)
- `created_at`, `updated_at`

Índice importante:

- Unique: (`campaign_run_id`, `channel`, `destination`) — `campaign_recipients_run_channel_dest_uq`

Model: `app/Models/Tenant/CampaignRecipient.php`

### `assets`

Migration: `database/migrations/tenant/2026_02_25_000010_create_assets_table.php`

- `id`
- `disk` (default `tenant_uploads`)
- `path`
- `filename`
- `mime`
- `size`
- `checksum_sha256` (nullable)
- `meta_json` (nullable)
- `created_by` (nullable)
- `created_at`, `updated_at`

Índice importante:

- Unique: (`disk`, `path`) — `assets_disk_path_unique`

Model: `app/Models/Tenant/Asset.php`

### `campaign_automation_locks`

Migration: `database/migrations/tenant/2026_02_25_000011_create_campaign_automation_locks_table.php`

- `id`
- `campaign_id` (FK → `campaigns`)
- `trigger` (string)
- `window_date` (date)
- `status` (`locked|done|error`)
- `run_id` (nullable, FK → `campaign_runs`)
- `error_message` (500, nullable)
- `created_at`, `updated_at`

Índices importantes:

- Unique: (`campaign_id`, `trigger`, `window_date`) — `campaign_automation_locks_uq`
- Index: (`status`, `window_date`) — `campaign_automation_locks_status_window_idx`

Model: `app/Models/Tenant/CampaignAutomationLock.php`

### `notification_deliveries` (auditoria)

Migration: `database/migrations/tenant/2026_02_25_000006_create_notification_deliveries_table.php`

- Usada para auditoria de entregas de email/whatsapp, inclusive campanhas.
- `meta` armazena campos como `campaign_id`, `campaign_run_id`, `campaign_recipient_id`, `channel`, `asset_id`, etc.

Model: `app/Models/Tenant/NotificationDelivery.php`

## Schemas JSON (versionados)

Os JSONs do módulo usam `version=1` hoje. Os campos abaixo refletem o que o backend valida e/ou consome.

### `campaigns.content_json` (v1)

Exemplo (email + whatsapp):

```json
{
  "version": 1,
  "email": {
    "subject": "Olá, {{ patient.first_name }}",
    "body_html": "<p>Mensagem HTML</p>",
    "body_text": "Mensagem texto",
    "attachments": [
      { "source": "upload", "asset_id": 123, "filename": "arquivo.pdf", "mime": "application/pdf", "size": 102400 }
    ]
  },
  "whatsapp": {
    "provider": "waha",
    "message_type": "media",
    "text": "",
    "media": { "kind": "image", "source": "upload", "url": "", "asset_id": 456, "caption": "Legenda {{ now.date }}" }
  }
}
```

Regras (fatuais):

- `email.subject` é obrigatório quando canal `email` está selecionado.
- Para `email`, é necessário preencher ao menos um entre `body_html` e `body_text`.
- `whatsapp.provider` validado como `waha` no request.
- `whatsapp.message_type` ∈ {`text`, `media`}.
- Se `message_type=text`, `whatsapp.text` é obrigatório.
- Se `message_type=media`:
  - `media.kind` ∈ {`image`, `video`, `document`, `audio`}
  - `media.source` ∈ {`url`, `upload`}
  - `media.url` é obrigatório quando `source=url`
  - `media.asset_id` é obrigatório quando `source=upload`

### `campaigns.audience_json` (v1)

O form atual grava audiência fixa de pacientes ativos com flags automáticas (conforme canais).

```json
{
  "version": 1,
  "source": "patients",
  "filters": { "patient": { "is_active": 1 } },
  "require": { "email": 1, "whatsapp": 0 }
}
```

### `campaigns.automation_json` (v1)

Apenas para `type=automated`.

```json
{
  "version": 1,
  "trigger": "birthday",
  "timezone": "America/Campo_Grande",
  "schedule": { "type": "daily", "time": "09:00" }
}
```

### `campaign_runs.context_json`

O `CampaignStarter` grava contexto de disparo e snapshot de audiência.

```json
{
  "trigger": "manual",
  "channels": ["email"],
  "audience_snapshot": { "version": 1, "source": "patients" },
  "initiated_by": 10,
  "initiated_at": "2026-02-25T12:34:56-04:00"
}
```

### `campaign_recipients.vars_json`

Produzido por `CampaignAudienceBuilder`.

```json
{
  "patient": {
    "id": "123",
    "full_name": "Nome Completo",
    "first_name": "Nome",
    "email": "email@dominio.com",
    "phone": "+5567999999999",
    "is_active": true,
    "birthdate_day_month": "25/02"
  },
  "now": { "date": "2026-02-25" },
  "inactivity_days": 60
}
```

