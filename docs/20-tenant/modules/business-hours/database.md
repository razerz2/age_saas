# Business Hours — Database

## Models

- `App/Models/Tenant/BusinessHour.php`
  - Usado em `BusinessHourController`.
  - Campos inferidos a partir do controller:
    - `doctor_id`
    - `weekday`
    - `start_time`
    - `end_time`
    - `break_start_time`
    - `break_end_time`

- `App/Models/Tenant/Doctor.php`
  - Relacionamento com horários de atendimento (`$doctor->businessHours()` — inferido pelo uso de `where('doctor_id', ...)`).

## Tabelas / Migrations

- Tabela principal para horários de atendimento:
  - Nome não explicitamente visto no controller.
  - (não identificado no código — provável em `database/migrations/tenant/*business_hours*`).

## Relações relevantes

- Cada `BusinessHour` pertence a um médico (`doctor_id`).
- Módulos de agendamentos e recorrências utilizam `BusinessHour` para montar horários disponíveis.
