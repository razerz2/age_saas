# Tenant — Estrutura de Pastas (Índice)

Este arquivo é um **mapa rápido** da estrutura de código da área Tenant.

## Mapa rápido

- **Rotas**
  - `routes/tenant.php` — todas as rotas públicas e autenticadas do Tenant.

- **Controllers**
  - `app/Http/Controllers/Tenant/*` — controllers por módulo (Appointments, RecurringAppointment, Form, FormResponse, etc.).

- **Models**
  - `app/Models/Tenant/*` — models por módulo (Appointment, RecurringAppointment, Form, FormSection, FormQuestion, QuestionOption, FormResponse, ResponseAnswer, ...).

- **Views**
  - `resources/views/tenant/*` — views organizadas por módulo (`appointments`, `forms`, `form_responses`, ...).

- **Frontend (JS/CSS)**
  - `resources/js/tenant/pages/*` — entradas JS por módulo (`appointments.js`, `recurring-appointments.js`, ...).
  - `resources/css/tenant/pages/*` — CSS por módulo (`appointments.css`, `recurring-appointments.css`, ...).

## Links para módulos (detalhes)

- **Appointments**
  - Rotas: `docs/20-tenant/modules/appointments/routes.md`.
  - Views: `docs/20-tenant/modules/appointments/views.md`.
  - Backend (controllers/requests/services): `docs/20-tenant/modules/appointments/backend.md`.
  - Frontend (JS/CSS/padrões de UI): `docs/20-tenant/modules/appointments/frontend.md`.
  - Database (tabelas/migrations): `docs/20-tenant/modules/appointments/database.md`.

- **Recurring Appointments**
  - Rotas: `docs/20-tenant/modules/recurring-appointments/routes.md`.
  - Views: `docs/20-tenant/modules/recurring-appointments/views.md`.
  - Backend: `docs/20-tenant/modules/recurring-appointments/backend.md`.
  - Frontend: `docs/20-tenant/modules/recurring-appointments/frontend.md`.
  - Database: `docs/20-tenant/modules/recurring-appointments/database.md`.

- **Forms**
  - Rotas: `docs/20-tenant/modules/forms/routes.md`.
  - Views: `docs/20-tenant/modules/forms/views.md`.
  - Backend: `docs/20-tenant/modules/forms/backend.md`.
  - Frontend: `docs/20-tenant/modules/forms/frontend.md`.
  - Database: `docs/20-tenant/modules/forms/database.md`.

- **Form Responses**
  - Rotas: `docs/20-tenant/modules/form-responses/routes.md`.
  - Views: `docs/20-tenant/modules/form-responses/views.md`.
  - Backend: `docs/20-tenant/modules/form-responses/backend.md`.
  - Frontend: `docs/20-tenant/modules/form-responses/frontend.md`.
  - Database: `docs/20-tenant/modules/form-responses/database.md`.

## Documentação relacionada

- `ARQUITETURA.md` — estrutura geral de pastas, rotas e controllers.
- `TENANT.md` — visão funcional completa da área Tenant.
