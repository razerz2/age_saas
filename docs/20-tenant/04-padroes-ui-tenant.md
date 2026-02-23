# Padrões de UI — Tenant (Índice)

Este arquivo é um **índice** dos padrões de UI/UX aplicados à área Tenant.

## Padrões globais

- Padrões gerais de frontend, dark mode e TailAdmin:
  - `docs/00-global/03-padroes-frontend.md`.
- Encoding, CSS escopado e boas práticas gerais:
  - `docs/00-global/02-padroes-codigo.md`.

Detalhes finos de layout (Grid.js, cards, dark mode) continuam centralizados nos documentos globais e técnicos.

## Padrões específicos já aplicados (exemplos por módulo)

- **Grid.js e listagens**
  - Uso de `grid-data` em rotas dedicadas e componentes Blade com colunas `status_badge` e `actions`.
  - Exemplos:
    - `docs/20-tenant/modules/appointments/frontend.md`.
    - `docs/20-tenant/modules/recurring-appointments/frontend.md`.
    - `docs/20-tenant/modules/forms/frontend.md`.
    - `docs/20-tenant/modules/form-responses/frontend.md`.

- **Partials de status e actions**
  - Padrão de colunas HTML encapsuladas em partials (`status`, `actions`) consumidas pelo Grid.js.
  - Exemplos:
    - `docs/20-tenant/modules/appointments/views.md` (partials em `tenant.appointments.partials.*`).
    - `docs/20-tenant/modules/recurring-appointments/views.md` (partials em `tenant.appointments.recurring.partials.*`).
    - `docs/20-tenant/modules/forms/views.md` (partials em `tenant.forms.partials.*`).
    - `docs/20-tenant/modules/form-responses/views.md` (partials em `tenant.form_responses.partials.*`, quando aplicável).

- **Assets por módulo (JS/CSS)**
  - Para páginas com comportamento específico, usar arquivos dedicados em `resources/js/tenant/pages/*` e `resources/css/tenant/pages/*`.
  - Exemplos:
    - `appointments.js` / `appointments.css` → ver `docs/20-tenant/modules/appointments/frontend.md`.
    - `recurring-appointments.js` / `recurring-appointments.css` → ver `docs/20-tenant/modules/recurring-appointments/frontend.md`.

## Onde aprofundar

- Padrão completo de views, Grid.js e formulários do Tenant:
  - `TENANT.md` (seções de padrão de views, Grid.js, formulários e CSS por módulo).
- Detalhes específicos por módulo:
  - `docs/20-tenant/modules/*/views.md`.
  - `docs/20-tenant/modules/*/frontend.md`.

> Este arquivo não repete as regras completas; ele apenas aponta para onde cada padrão já está descrito e exemplificado.
