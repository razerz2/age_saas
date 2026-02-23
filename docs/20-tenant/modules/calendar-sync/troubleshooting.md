# Calendar Sync — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `CalendarSyncStateController`.

- O controller utiliza principalmente redirects com mensagens de sucesso (`with('success', ...)`) e não possui mensagens de erro customizadas explícitas nas partes inspecionadas.

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado no guard `tenant`.
- Confirmar se o agendamento associado (`appointment_id`) existe e está acessível ao usuário atual.
- Conferir se migrations de `calendar_sync_states` foram executadas (`database/migrations/tenant/*calendar_sync_states*`).
- Validar se os dados enviados nos forms de create/edit correspondem aos campos esperados pelo `StoreCalendarSyncStateRequest`/`UpdateCalendarSyncStateRequest`.
- Revisar logs de aplicação para eventuais exceções durante criação/edição de estados de sync.
