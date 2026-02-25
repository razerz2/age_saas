# Troubleshooting

## 1) Slots não aparecem

Checklist:

- Verifique se médico tem business hours no dia selecionado.
- Teste endpoint diretamente:

```bash
# Interno
GET /workspace/{slug}/api/doctors/{doctorId}/available-slots?date=YYYY-MM-DD

# Público
GET /customer/{slug}/agendamento/api/doctors/{doctorId}/available-slots?date=YYYY-MM-DD
```

Observação:

- Interno retorna `422` para data passada.

## 2) Waitlist não entra

Checklist:

- `appointments.waitlist.enabled` deve estar `true`.
- Slot não pode estar `FREE` (FREE deve virar agendamento normal).
- Se slot for BUSY e `appointments.waitlist.allow_when_confirmed=false`, a entrada é bloqueada.
- Se `appointments.waitlist.max_per_slot` foi atingido, novas entradas são recusadas.

Arquivos para validar regra:

- `app/Services/Tenant/WaitlistService.php`
- `app/Http/Controllers/Tenant/AppointmentController.php`
- `app/Http/Controllers/Tenant/PublicAppointmentController.php`

## 3) Oferta não chega

Verifique scheduler e comandos:

```bash
php artisan schedule:list
php artisan appointments:expire-pending
php artisan appointments:expire-waitlist-offers
```

Scheduler esperado em `app/Console/Kernel.php`:

- `appointments:expire-pending` a cada 5 min
- `appointments:expire-waitlist-offers` a cada 5 min

Também verifique a auditoria persistente no banco do tenant:

- `notification_deliveries` com `key=waitlist.offered` e `status=success|error`.
- Para detalhes do schema e flags LGPD, veja: `docs/20-tenant/modules/notification-templates/database.md`.

## 4) Horário fica preso em HOLD

Checklist:

- Confirme `appointments.confirmation.enabled` e `appointments.confirmation.ttl_minutes`.
- Verifique se `confirmation_expires_at` existe e se job de expiração está rodando.
- Rode manualmente:

```bash
php artisan appointments:expire-pending
```

## 5) Paciente não consegue confirmar

Checklist:

- Token correto (`confirmation_token`) no link.
- Appointment ainda em `pending_confirmation`.
- Prazo não vencido (`now < confirmation_expires_at`).
- Rotas públicas de confirm/cancel ativas em `routes/tenant.php`.

## 6) Oferta expira e não passa para próximo

Checklist:

- Job `ExpireWaitlistOffersJob` executando.
- Status da entry deve sair de `OFFERED` para `EXPIRED`.
- Service deve chamar `offerNext` para o mesmo slot.

Comando manual:

```bash
php artisan appointments:expire-waitlist-offers
```

## 7) Notificações não respeitam o Editor

Checklist:

- Confirme se existe override para `(tenant_id, channel, key)` em `notification_templates`.
- Confirme a key disparada no evento:
  - `appointment.pending_confirmation`, `appointment.confirmed`, `appointment.canceled`, `appointment.expired`,
  - `waitlist.joined`, `waitlist.offered`.
- Verifique `notification_deliveries.meta.template_source` (deve ser `override` quando existir override).

## Checklist de regressão

1. FREE + hold OFF:
- submit cria appointment `scheduled`.

2. FREE + hold ON:
- submit cria `pending_confirmation` com `confirmation_expires_at`.
- confirmar antes do prazo muda para `scheduled`.
- após prazo, expira para `expired`.

3. Cancelar appointment:
- muda para `canceled`.
- dispara `onSlotReleased` para possível oferta.

4. HOLD/BUSY no form (interno/público):
- UI seta `intent_waitlist=1`.
- submit entra em waitlist e não cria appointment.

5. Oferta da fila:
- primeiro WAITING recebe OFFERED (FIFO).
- aceitar oferta cria appointment com regra de hold (on/off).

6. Expiração de oferta:
- OFFERED vencido -> EXPIRED -> próximo WAITING ofertado.

7. Regras de waitlist:
- `allow_when_confirmed=false` bloqueia entrada quando slot BUSY.
- `max_per_slot` limita WAITING+OFFERED por slot.

8. Recorrência:
- confirmar explicitamente que não houve mudança funcional no módulo de recorrência.

