# Módulo: Online Appointments

Mapa rápido do módulo de agendamentos online do Tenant.

## Arquivos deste módulo

- `overview.md` — visão geral do módulo.
- `routes.md` — rotas autenticadas relacionadas a agendamentos online.
- `views.md` — telas e partials Blade do módulo.
- `backend.md` — controllers, services, models e helpers usados.
- `frontend.md` — JS/CSS associados e padrões de UI.
- `database.md` — modelos e tabelas relacionadas.
- `permissions.md` — guard/middlewares/regras de acesso.
- `troubleshooting.md` — problemas comuns e checklist de diagnóstico.

## Fontes consultadas (paths)

- `routes/tenant.php` (bloco ONLINE APPOINTMENTS).
- `app/Http/Controllers/Tenant/OnlineAppointmentController.php`.
- `app/Models/Tenant/Appointment.php`.
- `app/Models/Tenant/OnlineAppointmentInstruction.php`.
- `app/Models/Tenant/TenantSetting.php`.
- `app/Services/MailTenantService.php`.
- `app/Services/WhatsappTenantService.php`.
- `resources/views/tenant/online_appointments/*.blade.php`.
- `resources/js/tenant/pages/online_appointments.js`.
- `resources/css/tenant/pages/online_appointments.css`.
