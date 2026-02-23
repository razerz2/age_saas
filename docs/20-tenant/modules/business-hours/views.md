# Business Hours — Views

Fontes: `BusinessHourController`.

## Views principais

- `resources/views/tenant/business-hours/index.blade.php`
  - Lista horários de atendimento por médico e dia da semana.

- `resources/views/tenant/business-hours/create.blade.php`
  - Formulário para criar novos horários (múltiplos dias por vez).

- `resources/views/tenant/business-hours/show.blade.php`
  - Detalhes de um horário específico.

- `resources/views/tenant/business-hours/edit.blade.php`
  - Edição de um horário existente.

## Partials

- `resources/views/tenant/business-hours/partials/actions.blade.php`
  - Ações por linha em listagens (usado na resposta de `gridData`).

## Layouts e componentes

- Layout base específico não foi inspecionado — provavelmente herda do layout Tenant padrão.
- Padrões globais de UI em `docs/00-global/03-padroes-frontend.md`.
