# Appointment Types — Views

Fontes: `AppointmentTypeController`.

## Views principais

- `resources/views/tenant/appointment-types/index.blade.php`
  - Lista tipos de atendimento e permite filtro por médico.

- `resources/views/tenant/appointment-types/create.blade.php`
  - Formulário para criar novo tipo de atendimento.

- `resources/views/tenant/appointment-types/show.blade.php`
  - Detalhes de um tipo de atendimento específico.

- `resources/views/tenant/appointment-types/edit.blade.php`
  - Edição de um tipo de atendimento existente.

## Partials

- `resources/views/tenant/appointment-types/partials/actions.blade.php`
  - Ações por linha em listagens (usado em `gridData`).

## Layouts e componentes

- Layout base específico não foi inspecionado — provavelmente herda do layout Tenant padrão.
- Padrões globais de UI em `docs/00-global/03-padroes-frontend.md`.
