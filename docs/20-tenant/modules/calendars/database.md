# Calendars — Database

## Models

- `App/Models/Tenant/Calendar.php`
  - Usado em `CalendarController` e `AppointmentController::events`.
  - Relacionamentos usados no controller:
    - `doctor` → `Calendar::with('doctor.user')`.

- `App/Models/Tenant/Doctor.php`
  - Relacionamento com calendários (`$doctor->calendars()`).

## Tabelas / Migrations

- Tabela principal para calendários:
  - Nome não explicitamente visto no código do controller.
  - (não identificado no código — provável em `database/migrations/tenant/*calendar*`).

## Relações relevantes

- Calendário pertence a um médico (`doctor_id`).
- Módulo de agendamentos (`AppointmentController::events`) consome `calendar_id` e `doctor_id` para filtrar eventos.
