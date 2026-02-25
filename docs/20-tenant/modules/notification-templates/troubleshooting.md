# Troubleshooting

## 1) “Template não muda” (continua igual ao padrão)

Checklist:

- Confirme se existe registro em `notification_templates` para `(tenant_id, channel, key)`.
- Confirme se você está editando o canal correto:
  - `whatsapp` e `email` são overrides separados.
- Confirme se a key é a mesma disparada no evento:
  - `appointment.pending_confirmation`
  - `appointment.confirmed`
  - `appointment.canceled`
  - `appointment.expired`
  - `waitlist.joined`
  - `waitlist.offered`

## 2) “Restaurar padrão” não volta ao default

- Restaurar padrão = delete do override no banco.
- Após restaurar, a tela deve recarregar via `GET /workspace/{slug}/settings?tab=editor&channel=...&key=...`.

## 3) Placeholders não substituem

Comportamento esperado:

- Se o placeholder não existir no contexto, ele permanece intacto no texto.

Verificações:

- Preview do Editor mostra lista de placeholders desconhecidos (não bloqueante).
- Logs podem conter warning `unknown_placeholders` com a lista.

## 4) Preview mostra contexto “mock”

Isso acontece quando:

- Não existe Appointment recente no tenant (preview usa contexto básico), ou
- Ocorreu falha ao montar contexto real.

Para keys `waitlist.*`, o preview aplica fallback (ex.: `waitlist.offer_expires_at`) mesmo sem entry real.

## 5) WhatsApp/Email “real” não respeita o Editor

Checklist:

- O envio real deve sair do pipeline: template efetivo -> contexto -> render -> sender.
- Verifique `notification_deliveries`:
  - `key`
  - `channel`
  - `meta.template_source` (`override` vs `default`)
  - `status` + `error_message` quando falha.

## 6) Auditoria não grava no banco

Checklist:

- Rodar migrations do tenant (inclui `notification_deliveries`).
- Se a tabela não existir, o logger é best-effort e não quebra o envio.

Flags:

- `NOTIFICATION_STORE_BODY=true` armazena `subject_raw/message_raw` (use apenas para debug controlado).

