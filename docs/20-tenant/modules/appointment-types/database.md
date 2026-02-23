# Appointment Types — Database

## Models

- `App/Models/Tenant/AppointmentType.php`
  - Usado em `AppointmentTypeController`.
  - Campos usados/derivados no controller:
    - `doctor_id`
    - `name`
    - `duration_min`
    - `price` (acessado como `price` derivado de `price_cents` ou campo equivalente)
    - `color`

- `App/Models/Tenant/Doctor.php`
  - Relacionamento com tipos de atendimento (`appointmentTypes`) usado para filtros e seleção.

## Tabelas / Migrations

- Tabela principal para tipos de atendimento:
  - Nome não explicitamente visto no controller.
  - (não identificado no código — provável em `database/migrations/tenant/*appointment_types*`).

## Relações relevantes

- Cada `AppointmentType` pertence a um médico (`doctor_id`).
- O módulo de agendamentos utiliza `AppointmentType` para determinar duração e exibir informações de consulta.
