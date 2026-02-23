# Online Appointments — Database

## Models

- `App/Models/Tenant/Appointment.php`
  - Filtrado por `appointment_mode = 'online'`.
  - Relacionamentos usados:
    - `patient`
    - `calendar.doctor.user`
    - `type`
    - `specialty`
    - `onlineInstructions`

- `App/Models/Tenant/OnlineAppointmentInstruction.php`
  - Associado a `Appointment` (`appointment_id`).
  - Campos usados no controller:
    - `meeting_link`
    - `meeting_app`
    - `general_instructions`
    - `patient_instructions`
    - `sent_by_email_at`
    - `sent_by_whatsapp_at`

- `App/Models/Tenant/TenantSetting.php`
  - Usado para ler configurações de modo de agendamento e notificações.

## Tabelas / Migrations

- Tabela de agendamentos (`appointments`):
  - Já usada amplamente em outros módulos.

- Tabela de instruções de agendamentos online:
  - Nome não explicitamente visto no controller.
  - (não identificado no código — provável em `database/migrations/tenant/*online_appointment_instructions*`).

## Relações relevantes

- `OnlineAppointmentInstruction` pertence a um `Appointment`.
- `Appointment` possui um relacionamento `onlineInstructions` usado em todas as operações do módulo.
