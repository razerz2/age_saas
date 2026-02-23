# Online Appointments — Views

Fontes: `OnlineAppointmentController`.

## Views principais

- `resources/views/tenant/online_appointments/index.blade.php`
  - Lista agendamentos com `appointment_mode = 'online'`.

- `resources/views/tenant/online_appointments/show.blade.php`
  - Formulário para configurar instruções (link, app, instruções gerais e para o paciente).

## Partials

- `resources/views/tenant/online_appointments/partials/status.blade.php`
- `resources/views/tenant/online_appointments/partials/instructions.blade.php`
- `resources/views/tenant/online_appointments/partials/actions.blade.php`

Esses partials são usados na resposta JSON de `gridData` para compor colunas HTML no Grid.js.

## Layouts e componentes

- Layout base específico não foi inspecionado — provavelmente herda do layout Tenant padrão.
- Padrões globais de UI em `docs/00-global/03-padroes-frontend.md`.
