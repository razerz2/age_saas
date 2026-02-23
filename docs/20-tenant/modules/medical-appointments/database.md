# Medical Appointments — Database

## Models

- `App/Models/Tenant/Appointment.php`
  - Usado extensivamente em `MedicalAppointmentController`.
  - Relacionamentos usados:
    - `calendar.doctor.user`
    - `patient`
    - `type`
    - `specialty`

- `App/Models/Tenant/Doctor.php`
  - Usado para recuperar médicos associados ao usuário e filtrar agendamentos.

- `App/Models/Tenant/Form.php`
  - Usado para encontrar formulários ligados a um agendamento (`Form::getFormForAppointment`).

- `App/Models/Tenant/FormResponse.php`
  - Usado para buscar respostas submetidas de formulários associados ao agendamento.

- `App/Models/Platform/Tenant.php`
  - Usado em `ensureTenantConnection()` para garantir conexão correta com o banco do Tenant.

## Tabelas / Migrations

- Tabelas principais:
  - `appointments`
  - `doctors`
  - `forms`
  - `form_responses`

- Migrations específicas do fluxo de atendimento:
  - (não identificado no código — provável em `database/migrations/tenant/*appointments*`, `*forms*`, `*form_responses*`).

## Relações relevantes

- Cada `Appointment` pertence a um calendário (`calendar_id`), que pertence a um médico.
- `MedicalAppointmentController` utiliza essas relações para filtrar agendamentos visíveis de acordo com o papel (`role`) do usuário.
- Formulários e respostas são carregados apenas quando existem associações válidas para o agendamento.
