# Calendars — Views

Fontes: `CalendarController`.

## Views principais

- `resources/views/tenant/calendars/index.blade.php`
  - Lista de calendários do Tenant.

- `resources/views/tenant/calendars/create.blade.php`
  - Formulário de criação de calendário (associação médico ↔ agenda).

- `resources/views/tenant/calendars/show.blade.php`
  - Detalhes de um calendário específico.

- `resources/views/tenant/calendars/edit.blade.php`
  - Edição de calendário existente.

## Partials

- `resources/views/tenant/calendars/partials/actions.blade.php`
  - Ações por linha em listagens (usado em Grid.js através de `gridData`).

## Layouts e componentes

- Layout base específico não foi inspecionado aqui — provavelmente herda do layout Tenant padrão.
- Padrões de UI globais estão em `docs/00-global/03-padroes-frontend.md`.
