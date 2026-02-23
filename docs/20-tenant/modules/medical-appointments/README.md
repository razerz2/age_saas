# Módulo: Medical Appointments

Mapa rápido do módulo de atendimento médico (sessão de atendimento do dia) do Tenant.

## Arquivos deste módulo

- `overview.md` — visão geral do módulo.
- `routes.md` — rotas relacionadas ao atendimento médico.
- `views.md` — telas e partials Blade do módulo.
- `backend.md` — controller, models e serviços usados.
- `frontend.md` — JS/CSS associados e padrões de UI.
- `database.md` — modelos e tabelas relacionadas.
- `permissions.md` — guard/middlewares/regras de acesso.
- `troubleshooting.md` — problemas comuns e checklist de diagnóstico.

## Fontes consultadas (paths)

- `routes/tenant.php` (bloco de atendimento médico / medical appointments).
- `app/Http/Controllers/Tenant/MedicalAppointmentController.php`.
- `app/Models/Tenant/Appointment.php`.
- `app/Models/Tenant/Doctor.php`.
- `app/Models/Tenant/Form.php`.
- `app/Models/Tenant/FormResponse.php`.
- `app/Models/Platform/Tenant.php` (para conexão de Tenant).
- `resources/views/tenant/medical_appointments/*.blade.php` (incluindo partials como `partials/details` e `partials/form-response-modal`).
- `resources/js/tenant/pages/medical_appointments.js`.
- `resources/css/tenant/pages/medical_appointments.css`.
