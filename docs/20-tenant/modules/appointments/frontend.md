# Frontend

## Escopo de UI

Interno:

- `resources/js/tenant/pages/appointments.js`
- `resources/views/tenant/appointments/create.blade.php`
- `resources/views/tenant/appointments/edit.blade.php`

Público:

- `resources/js/tenant/pages/public.js`
- `resources/views/tenant/public/appointment-create.blade.php`
- `resources/views/tenant/public/appointment-offer.blade.php`

## Interno (appointments.js + create/edit)

### Entrada de dados

- Consome endpoint interno de slots:
  - `GET /workspace/{slug}/api/doctors/{doctorId}/available-slots`

### Compatibilidade de payload

`appointments.js` tenta nesta ordem:

- `data.slots`
- fallback legado: `data.available`
- fallback legado antigo: `data.data`
- array direto

### Render de horários

- Renderiza todos os slots no `#appointment_time`.
- Usa `data-*` por option:
  - `data-starts-at`
  - `data-ends-at`
  - `data-status`
  - `data-hold-expires-at`
  - mantém `data-start`/`data-end` para compat

Status na UI:

- `FREE`: normal
- `HOLD`: selecionável, label “Reservado” (+ expiração quando existe)
- `BUSY`: selecionável, label “Ocupado”
- `DISABLED`: `option.disabled=true`

### Waitlist intent (`intent_waitlist`)

Views possuem:

- hidden `#intent_waitlist`
- hidden `#starts_at`
- hidden `#ends_at`
- alerta `#slot_waitlist_alert` + `#slot_waitlist_alert_message`

Comportamento:

- Se seleciona HOLD/BUSY -> `intent_waitlist=1` + exibe alerta.
- Se seleciona FREE -> `intent_waitlist=0` + esconde alerta.
- Se seleciona DISABLED -> limpa seleção e hidden fields.

## Público (public.js + appointment-create)

### Entrada de dados

- Consome endpoint público de slots:
  - `GET /customer/{slug}/agendamento/api/doctors/{doctorId}/available-slots`

### Compatibilidade de payload

`public.js` tenta nesta ordem:

- `data.slots`
- fallback: `data.available`
- fallback: `data.data`
- array direto

### Render de horários

- Renderiza todos os slots no `#appointment_time`.
- Labels:
  - HOLD: “(Reservado)” com “até HH:mm” se houver `hold_expires_at`
  - BUSY: “(Ocupado)”
  - DISABLED: “(Indisponível - reason)” e `disabled=true`

### Waitlist intent

View pública possui:

- hidden `#intent_waitlist`
- hidden `#starts_at` / `#ends_at`
- alerta `#slot_waitlist_alert` com mensagem de fila

No `change` do horário:

- HOLD/BUSY -> `intent_waitlist=1` + alerta
- FREE/vazio -> `intent_waitlist=0` + alerta oculto
- DISABLED -> limpa seleção + hidden

## Tela de oferta pública

Arquivo:

- `resources/views/tenant/public/appointment-offer.blade.php`

Comportamento:

- Exibe dados da oferta (profissional, início/fim, validade).
- Se oferta válida: botão POST para aceitar (`public.waitlist.offer.accept`).
- Se inválida: mostra aviso de indisponibilidade.

## Notificações e auditoria (observabilidade)

- O envio real de WhatsApp/Email usa templates efetivos (default/override) e placeholders renderizados.
- Toda tentativa de envio (success/error) é registrada no banco do tenant em `notification_deliveries`.
- Veja o módulo: `docs/20-tenant/modules/notification-templates/overview.md`.

## Nota de recorrência

- Não houve mudança de UI no módulo de recorrência.

