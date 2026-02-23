# Calendar Sync — Database

## Models

- `App/Models/Tenant/CalendarSyncState.php`
  - Usado em `CalendarSyncStateController`.
  - Relacionamentos usados no controller:
    - `appointment` → carregado com `appointment.patient` e `appointment.calendar.doctor.user`.

- `App/Models/Tenant/Appointment.php`
  - Utilizado para listar agendamentos disponíveis ao criar/editar estados de sync.

## Tabelas / Migrations

- Tabela principal para estados de sincronização de calendário:
  - Nome não explicitamente visto no controller.
  - (não identificado no código — provável em `database/migrations/tenant/*calendar_sync_states*`).

## Relações relevantes

- Cada `CalendarSyncState` pertence a um agendamento (`appointment_id`).
- As views exibem informações combinadas de `appointment`, `patient` e `doctor` relacionados ao estado de sync.
