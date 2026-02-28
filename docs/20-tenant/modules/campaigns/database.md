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

Migrations:

- `database/migrations/tenant/2026_02_25_000011_create_campaign_automation_locks_table.php`
- `database/migrations/tenant/2026_02_28_000014_update_campaign_automation_locks_for_minute_window.php`

- `id`
- `campaign_id` (FK → `campaigns`)
- `trigger` (string, legado/deprecated)
- `window_date` (date, legado/deprecated)
- `window_key` (string `YYYY-MM-DD HH:MM`) — fonte de verdade para lock por minuto
- `timezone` (string, opcional)
- `status` (`locked|done|error`)
- `run_id` (nullable, FK → `campaign_runs`)
- `error_message` (500, nullable)
- `created_at`, `updated_at`

Índices importantes:

- Unique: (`campaign_id`, `window_key`) — `campaign_automation_locks_campaign_window_uq`
- Index: (`status`, `window_key`) — `campaign_automation_locks_status_window_key_idx`

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


## Atualização Etapa 1 (Agendada)

A partir da migração `database/migrations/tenant/2026_02_28_000012_add_scheduling_fields_to_campaigns_table.php`, campanhas `type=automated` passam a usar os campos abaixo como fonte de verdade de programação:

- `schedule_mode` (`period|indefinite`)
- `starts_at` (datetime)
- `ends_at` (datetime nullable quando `indefinite`)
- `schedule_weekdays` (json array com padrão `0..6`, onde `0=domingo`)
- `schedule_times` (json array `HH:MM`, sem duplicados)
- `timezone` (IANA timezone)

Compatibilidade:

- `automation_json` permanece no schema como legado (deprecated), para leitura de dados antigos.
- `scheduled_at` permanece no schema para o agendamento pontual de disparo manual (fluxo de ações de dispatch).
- Para campanhas automáticas/agendadas, a programação recorrente deve considerar `schedule_*` como fonte de verdade.

## Atualizacao Etapa 2 (Regras opcionais)

A partir da migracao `database/migrations/tenant/2026_02_28_000013_add_rules_json_to_campaigns_table.php`, campanhas agendadas podem persistir filtros opcionais em `rules_json`:

- `rules_json.logic` (`AND|OR`)
- `rules_json.conditions[]` com:
  - `field` em whitelist
  - `op` em whitelist
  - `value` quando aplicavel

Formato padrao:

```json
{
  "logic": "AND",
  "conditions": [
    {"field":"gender","op":"=","value":"F"},
    {"field":"is_active","op":"=","value":true}
  ]
}
```

As regras sao aplicadas no `CampaignAudienceBuilder` como complemento dos filtros de audiencia existentes.
