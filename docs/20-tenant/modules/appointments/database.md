# Database

## Migrations adicionadas/alteradas para o módulo

### 1) Settings de hold/waitlist

Arquivo:

- `database/migrations/tenant/2026_02_24_000001_add_appointment_confirmation_waitlist_settings.php`

Efeito:

- Cria defaults em `tenant_settings` para:
  - `appointments.confirmation.enabled=false`
  - `appointments.confirmation.ttl_minutes=30`
  - `appointments.waitlist.enabled=false`
  - `appointments.waitlist.offer_ttl_minutes=15`
  - `appointments.waitlist.allow_when_confirmed=true`
  - `appointments.waitlist.max_per_slot=null`

### 2) Campos de confirmação/cancelamento/expiração em appointments

Arquivo:

- `database/migrations/tenant/2026_02_24_000002_add_confirmation_fields_to_appointments_table.php`

Campos adicionados em `appointments`:

- `confirmation_expires_at` (datetime, nullable)
- `confirmed_at` (datetime, nullable)
- `canceled_at` (datetime, nullable)
- `expired_at` (datetime, nullable)
- `cancellation_reason` (text, nullable)

### 3) Tabela de fila de espera

Arquivo:

- `database/migrations/tenant/2026_02_24_000003_create_appointment_waitlist_entries_table.php`

Tabela `appointment_waitlist_entries`:

- `id` (uuid, PK)
- `tenant_id` (uuid, index)
- `doctor_id` (foreignUuid -> doctors, cascadeOnDelete)
- `patient_id` (foreignUuid -> patients, cascadeOnDelete)
- `starts_at` (datetime)
- `ends_at` (datetime)
- `status` (string 32)
- `offer_token` (string nullable, unique)
- `offered_at` (datetime nullable)
- `offer_expires_at` (datetime nullable)
- `accepted_at` (datetime nullable)
- `timestamps`

Índices:

- index `(tenant_id, doctor_id, starts_at, status)`
- unique anti-duplicação `(tenant_id, doctor_id, patient_id, starts_at, ends_at)`

### 4) Token de confirmação em appointments

Arquivo:

- `database/migrations/tenant/2026_02_24_000004_add_confirmation_token_to_appointments_table.php`

Campo:

- `confirmation_token` (string nullable, unique)

## Templates e auditoria de notificações (relacionado)

Este módulo dispara notificações por key (appointment.* e waitlist.*). Os dados do Editor e auditoria ficam em tabelas próprias.

### Overrides de templates (por tenant)

Arquivo:

- `database/migrations/tenant/2026_02_24_000005_create_notification_templates_table.php`

Tabela `notification_templates`:

- unique `(tenant_id, channel, key)`

### Auditoria de entregas (por tenant)

Arquivo:

- `database/migrations/tenant/2026_02_25_000006_create_notification_deliveries_table.php`

Tabela `notification_deliveries`:

- Registra toda tentativa de envio (success/error) com `channel`, `key`, `provider`, `status`, hashes e `meta`.
- Por padrão não armazena corpo completo (LGPD); corpo bruto só quando `NOTIFICATION_STORE_BODY=true`.

Ver detalhes no módulo:

- `docs/20-tenant/modules/notification-templates/database.md`

## Models

### `Appointment`

Arquivo:

- `app/Models/Tenant/Appointment.php`

Pontos principais:

- `fillable` inclui:
  - `confirmation_expires_at`, `confirmed_at`, `canceled_at`, `expired_at`, `cancellation_reason`, `confirmation_token`
- `casts` datetime para os campos de hold/cancel/expire.
- Helpers de slot:
  - `occupiesSlot()` => `scheduled|rescheduled|pending_confirmation`
  - `isHold()` => `pending_confirmation`
  - `isConfirmed()` => `scheduled|rescheduled`

### `AppointmentWaitlistEntry`

Arquivo:

- `app/Models/Tenant/AppointmentWaitlistEntry.php`

Pontos principais:

- Status constants:
  - `WAITING`, `OFFERED`, `ACCEPTED`, `EXPIRED`, `CANCELED`, `SKIPPED`
- `casts` datetime:
  - `starts_at`, `ends_at`, `offered_at`, `offer_expires_at`, `accepted_at`
- Método:
  - `isOfferValid()` => status OFFERED e `offer_expires_at > now`

## Observação de recorrência

- Nenhuma migration/model nova de recorrência foi adicionada para hold/waitlist.

