# Overview

## Escopo

Este módulo cobre o fluxo de agendamentos do tenant para:

- Interno (`/workspace/{slug}/appointments/*`)
- Público (`/customer/{slug}/agendamento/*`)

Inclui:

- Confirmação com prazo (hold)
- Waitlist por slot (`doctor_id + starts_at + ends_at`)
- Slots com status `FREE`, `HOLD`, `BUSY`, `DISABLED`
- Wiring de submit com `intent_waitlist`

Não inclui:

- Implementação no módulo de agendamento recorrente.

## Conceitos e status

### Appointment (`appointments.status`)

- `scheduled`, `rescheduled`: ocupam slot (BUSY).
- `pending_confirmation`: hold, ocupa slot (HOLD).
- `canceled`, `expired`: liberam slot.

Referência principal:

- `app/Models/Tenant/Appointment.php` (`occupiesSlot`, `isHold`, `isConfirmed`).

### Waitlist entry (`appointment_waitlist_entries.status`)

- `WAITING`: aguardando oferta
- `OFFERED`: oferta ativa com prazo
- `ACCEPTED`: oferta aceita
- `EXPIRED`: oferta expirou
- `CANCELED`: entrada cancelada
- `SKIPPED`: entrada pulada

Referência principal:

- `app/Models/Tenant/AppointmentWaitlistEntry.php`

## Settings do tenant

Defaults criados em:

- `database/migrations/tenant/2026_02_24_000001_add_appointment_confirmation_waitlist_settings.php`

Leitura via helper:

- `tenant_setting_bool`
- `tenant_setting_int`
- `tenant_setting_nullable_int`

Arquivo:

- `app/Helpers/ProfessionalHelper.php`

Chaves:

- `appointments.confirmation.enabled` (default `false`)
- `appointments.confirmation.ttl_minutes` (default `30`)
- `appointments.waitlist.enabled` (default `false`)
- `appointments.waitlist.offer_ttl_minutes` (default `15`)
- `appointments.waitlist.allow_when_confirmed` (default `true`)
- `appointments.waitlist.max_per_slot` (default `null`)

Tela de configuração:

- `app/Http/Controllers/Tenant/SettingsController.php` (`index`, `updateAppointments`)
- `resources/views/tenant/settings/tabs/agendamentos.blade.php`

## Fluxo funcional atual

1. Criação em slot FREE (`intent_waitlist=0`):
- Hold OFF: cria `scheduled` e `confirmed_at`.
- Hold ON: cria `pending_confirmation` com `confirmation_expires_at` e `confirmation_token`.

2. Confirmação:
- Interno: confirmação por `appointment`.
- Público: confirmação por `confirmation_token`.
- Se prazo vencido: muda para `expired` e libera slot.

3. Cancelamento:
- Interno/público atualizam para `canceled`.
- Ao cancelar, chama `WaitlistService::onSlotReleased(...)`.

4. Expiração de hold:
- Job expira `pending_confirmation` vencidos para `expired`.
- Depois tenta ofertar para slots liberados.

5. Waitlist:
- Submit com `intent_waitlist=1` não cria appointment.
- Entra na fila por slot (idempotente para WAITING/OFFERED).
- Quando slot libera, oferta para o primeiro WAITING (FIFO por `created_at`).
- Aceite de oferta cria appointment (com hold ou `scheduled` conforme setting).

6. Slots API:
- Retorna `slots[]` com todos os horários e status.
- Mantém `available[]` legado apenas com FREE.

## Notificações (keys)

As notificações ao paciente usam templates por key e podem ser personalizadas no Editor:

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

Veja:

- `docs/20-tenant/modules/notification-templates/overview.md`

## Nota sobre recorrência

- Não houve implementação de hold/waitlist no fluxo de recorrência.
- O endpoint interno de slots reflete bloqueio recorrente existente como `DISABLED` + `reason=RECURRING_BLOCK`.
- O endpoint público não aplica esse bloqueio de recorrência.

