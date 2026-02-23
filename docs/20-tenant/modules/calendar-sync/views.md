# Calendar Sync — Views

Fontes: `CalendarSyncStateController`.

## Views principais

- `resources/views/tenant/calendar-sync/index.blade.php`
  - Lista estados de sincronização (`syncStates`) com dados de agendamento e paciente.

- `resources/views/tenant/calendar-sync/create.blade.php`
  - Formulário para criar novo estado de sincronização, selecionando um agendamento.

- `resources/views/tenant/calendar-sync/show.blade.php`
  - Detalhes de um estado de sincronização específico.

- `resources/views/tenant/calendar-sync/edit.blade.php`
  - Edição de um estado de sincronização existente.

## Layouts e componentes

- Layout base específico não foi inspecionado — provavelmente herda do layout Tenant padrão.
- Padrões globais de UI em `docs/00-global/03-padroes-frontend.md`.
