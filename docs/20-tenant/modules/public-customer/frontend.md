# Public Customer — Frontend

## JS

- Arquivos JS dedicados em `resources/js/tenant/pages/*` para fluxo público (`/customer/{slug}`):
  - (não identificado no código — provável uso de JS específico em views públicas ou em arquivos compartilhados).

## CSS

- Arquivos CSS dedicados em `resources/css/tenant/pages/*` para fluxo público:
  - `resources/css/tenant/pages/public-appointments.css` — arquivo existente (não inspecionado) com estilos da área de agendamento público.
  - Demais arquivos CSS públicos:
    - (não identificado no código — revisar `resources/css/tenant/pages/public-*.css`).

## Observações

- As páginas públicas seguem o layout da área pública do Tenant, possivelmente distinto do layout autenticado do workspace.
- Padrões globais de UI (quando aplicáveis) estão em `docs/00-global/03-padroes-frontend.md`.
