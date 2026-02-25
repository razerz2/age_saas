# Backend

## 1) Settings e defaults

Fontes:

- `database/migrations/tenant/2026_02_24_000001_add_appointment_confirmation_waitlist_settings.php`
- `app/Helpers/ProfessionalHelper.php`
- `app/Http/Controllers/Tenant/SettingsController.php`
- `resources/views/tenant/settings/tabs/agendamentos.blade.php`

Comportamento:

- Defaults de hold/waitlist são salvos em `tenant_settings`.
- Leitura central por helper:
  - `tenant_setting_bool(...)`
  - `tenant_setting_int(...)`
  - `tenant_setting_nullable_int(...)`
- `SettingsController@updateAppointments` persiste os campos da seção “Agendamentos”.

## 2) Criação de appointment (interno/público)

Arquivos:

- `app/Http/Controllers/Tenant/AppointmentController.php`
- `app/Http/Controllers/Tenant/PublicAppointmentController.php`

### Decisão por `intent_waitlist`

- Se `intent_waitlist=1`: chama `WaitlistService::joinWaitlist(...)` e não cria appointment.
- Se `intent_waitlist!=1`: segue criação normal de appointment.

### Decisão por hold

- `appointments.confirmation.enabled=false`:
  - cria `status=scheduled`
  - define `confirmed_at=now()`
- `appointments.confirmation.enabled=true`:
  - cria `status=pending_confirmation`
  - define `confirmation_expires_at = now + ttl`
  - define `confirmation_token`

## 3) Confirmar e cancelar

Interno:

- `AppointmentController@confirm`
- `AppointmentController@cancel`

Público:

- `PublicAppointmentController@confirmByToken`
- `PublicAppointmentController@cancelByToken`

Regras:

- Confirmação valida status hold e prazo (`confirmation_expires_at`).
- Se prazo vencido, marca `expired`, dispara `appointment.expired` e libera slot.
- Cancelamento marca `canceled`, grava `canceled_at`, limpa hold e libera slot.
- Cancelamento/expiração chamam `WaitlistService::onSlotReleased(...)`.

## 4) Slots com status (JSON)

Arquivos:

- `AppointmentController@getAvailableSlots`
- `PublicAppointmentController@getAvailableSlots`

Contrato:

- Retorna `slots[]` (todos os horários gerados) com status `FREE/HOLD/BUSY/DISABLED`.
- Retorna `available[]` legado (somente FREE).
- `DISABLED` inclui `reason` (ex.: `BREAK`, `RECURRING_BLOCK`).
- `HOLD` inclui `hold_expires_at` quando existir.
- Interno inclui `appointment_id` para `HOLD`/`BUSY`.
- Público não retorna `appointment_id`.

Regras de ocupação:

- Ocupantes: `scheduled`, `rescheduled`, `pending_confirmation`.
- `pending_confirmation` => `HOLD`.
- `scheduled/rescheduled` => `BUSY`.
- Break => `DISABLED` (`reason=BREAK`).
- Interno: bloqueio recorrente existente => `DISABLED` (`reason=RECURRING_BLOCK`).

Observações:

- Interno retorna `422` para data passada (validação no endpoint).
- Público atualmente não bloqueia a consulta de slots para data passada (retorna conforme business hours/ocupação).

## 5) Waitlist backend

Arquivos:

- `app/Services/Tenant/WaitlistService.php`
- `app/Http/Controllers/Tenant/AppointmentWaitlistController.php`
- `app/Http/Controllers/Tenant/PublicAppointmentWaitlistController.php`

### `WaitlistService::joinWaitlist`

- Exige `appointments.waitlist.enabled=true`.
- Valida slot atual:
  - `FREE` => erro (orienta agendar normalmente).
  - `BUSY` com `allow_when_confirmed=false` => erro.
- Respeita `max_per_slot` (conta WAITING+OFFERED).
- Idempotência:
  - se já existe WAITING/OFFERED para mesmo paciente/slot, retorna sem duplicar.
- Dispara `waitlist.joined` quando cria entry nova.

### `WaitlistService::onSlotReleased` e `offerNext`

- Quando slot libera, tenta ofertar para o primeiro WAITING (FIFO por `created_at`).
- Marca entrada como OFFERED com:
  - `offer_token`
  - `offered_at`
  - `offer_expires_at = now + offer_ttl`
- Dispara notificação `waitlist.offered` via `NotificationDispatcher`.

### `WaitlistService::acceptOfferByToken`

- Executa em transação.
- Valida token e validade da oferta.
- Valida que slot ainda está livre.
- Cria appointment para `patient_id` da entry:
  - hold ON => `pending_confirmation`
  - hold OFF => `scheduled`
- Marca entry como `ACCEPTED` e grava `accepted_at`.

## 6) Jobs, commands e scheduler

Arquivos:

- `app/Jobs/Tenant/ExpirePendingAppointmentsJob.php`
- `app/Console/Commands/ExpirePendingAppointmentsCommand.php`
- `app/Jobs/Tenant/ExpireWaitlistOffersJob.php`
- `app/Console/Commands/ExpireWaitlistOffersCommand.php`
- `app/Console/Kernel.php`

Comandos:

- `appointments:expire-pending`
- `appointments:expire-waitlist-offers`

Agenda:

- Ambos agendados em `everyFiveMinutes()`.

Fluxo operacional:

- Expire pending:
  - `pending_confirmation` vencido -> `expired`
  - agrupa slots liberados e chama `onSlotReleased`
- Expire offers:
  - `OFFERED` vencido -> `EXPIRED`
  - tenta `offerNext` para o próximo WAITING do slot

## 7) Requests e validação

Arquivos:

- `app/Http/Requests/Tenant/StoreAppointmentRequest.php`
- `app/Http/Requests/Tenant/StorePublicAppointmentRequest.php`

Regras importantes:

- Ambos aceitam `intent_waitlist` em `0|1`.
- Quando `intent_waitlist=1`, pulam validações de conflito de agenda.
- Conflito de ocupação considera `scheduled`, `rescheduled`, `pending_confirmation`.

## 8) Notificações (chaves e disparos)

Integração:

- `app/Services/Tenant/NotificationDispatcher.php` gera payload por template (default/override), renderiza placeholders e entrega via:
  - `app/Services/Tenant/WhatsAppSender.php`
  - `app/Services/Tenant/EmailSender.php`
- Auditoria persistente (LGPD): `notification_deliveries` (ver módulo `notification-templates`).

Keys disparadas por evento (mínimo do fluxo atual):

- Criação com hold (`pending_confirmation`):
  - Interno: `AppointmentController@store` -> `appointment.pending_confirmation`
  - Público: `PublicAppointmentController@store` -> `appointment.pending_confirmation`
- Confirmação concluída:
  - Interno: `AppointmentController@confirm` -> `appointment.confirmed`
  - Público: `PublicAppointmentController@confirmByToken` -> `appointment.confirmed`
- Cancelamento:
  - Interno: `AppointmentController@cancel` -> `appointment.canceled`
  - Público: `PublicAppointmentController@cancelByToken` -> `appointment.canceled`
- Expiração do hold:
  - Job: `ExpirePendingAppointmentsJob` -> `appointment.expired`
  - Tentativa de confirmar após expirar:
    - Interno/público também marca `expired` e dispara `appointment.expired`
- Waitlist:
  - `WaitlistService::joinWaitlist` -> `waitlist.joined` (quando cria entry nova)
  - `WaitlistService::offerNext` -> `waitlist.offered` (quando gera oferta)

Para detalhes do Editor/keys/variáveis e auditoria, veja:

- `docs/20-tenant/modules/notification-templates/README.md`

## 9) Recorrência (não alterado)

- Não há integração de waitlist/hold no fluxo de criação recorrente.
- Apenas leitura de bloqueio recorrente existente no endpoint interno de slots.

