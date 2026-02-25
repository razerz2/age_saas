# Database

## 1) Overrides de templates (`notification_templates`)

Migration:

- `database/migrations/tenant/2026_02_24_000005_create_notification_templates_table.php`

Tabela: `notification_templates`

Colunas principais:

- `tenant_id` (uuid)
- `channel` (`email`|`whatsapp`)
- `key` (ex.: `appointment.confirmed`)
- `subject` (nullable; relevante apenas para email)
- `content` (longText)
- `timestamps`

Constraints/índices:

- unique `(tenant_id, channel, key)`
- index `(tenant_id, channel)`
- index `(tenant_id, key)`

Observação:

- Defaults não são gravados no banco; ficam apenas no config.

## 2) Auditoria de entregas (`notification_deliveries`)

Migration:

- `database/migrations/tenant/2026_02_25_000006_create_notification_deliveries_table.php`

Tabela: `notification_deliveries`

Objetivo:

- Registrar toda tentativa de envio real por canal/key/provider com status `success|error`.

Colunas principais:

- `tenant_id` (uuid)
- `channel` (`email`|`whatsapp`)
- `key` (ex.: `waitlist.offered`)
- `provider` (nullable; ex.: `mail:smtp`, `whatsapp:waha`)
- `status` (`success`|`error`)
- `sent_at` (datetime)
- `recipient` (nullable; armazenado mascarado)
- `subject` (nullable; por padrão null, ver flags)
- `subject_sha256` (nullable)
- `message_sha256` (nullable)
- `message_length` (nullable)
- `error_message` (nullable)
- `error_code` (nullable)
- `meta` (json; ex.: `appointment_id`, `waitlist_entry_id`, `template_source`, `is_override`, `unknown_placeholders`, etc.)
- `created_at`/`updated_at`

Índices:

- `(tenant_id, sent_at)`
- `(tenant_id, channel, key)`
- `(tenant_id, status)`
- `(tenant_id, provider)`

## LGPD e armazenamento de corpo

Por padrão:

- Não armazena o corpo completo no banco.
- Persiste apenas `sha256` e `length` + metadados.

Opcional (para diagnóstico local):

- Se `NOTIFICATION_STORE_BODY=true`, preenche:
  - `subject_raw`
  - `message_raw`

## Model e logger

- Model: `app/Models/Tenant/NotificationDelivery.php`
- Logger: `app/Services/Tenant/NotificationDeliveryLogger.php`
  - Operação best-effort: não deve quebrar envio caso a tabela não exista ou falhe o insert.

