# Reports - Database

Modelos/tabelas usados por relatorio:

## Agendamentos

- `appointments`
- `patients`
- `calendars`
- `doctors`
- `users` (nome do medico)
- `medical_specialties`
- `appointment_types`

## Pacientes

- `patients`
- `appointments` (withCount + filtros por data)

## Medicos

- `doctors`
- `users`
- `doctor_specialty` / `medical_specialties`
- `appointments` (withCount)

## Recorrencias

- `recurring_appointments`
- `doctors`
- `users`
- `patients`
- `appointment_types`

## Formularios

- `forms`
- `form_responses` (withCount)

## Portal do Paciente

- `patient_logins`
- `patients`

Nota: se `patient_logins` nao existir no tenant, o relatorio de portal responde vazio.

## Notificacoes

- `notifications`
