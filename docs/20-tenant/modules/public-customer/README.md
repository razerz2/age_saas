# Módulo: Public Customer

Mapa rápido do módulo público de agendamento e formulários (`/customer/{slug}`) do Tenant.

## Arquivos deste módulo

- `overview.md` — visão geral do módulo.
- `routes.md` — rotas públicas e APIs relacionadas.
- `views.md` — telas e partials Blade públicas.
- `backend.md` — controllers, models e helpers usados.
- `frontend.md` — JS/CSS associados (quando existirem).
- `database.md` — modelos e tabelas relacionadas.
- `permissions.md` — guard/middlewares/regras de acesso.
- `troubleshooting.md` — problemas comuns e checklist de diagnóstico.

## Fontes consultadas (paths)

- `routes/tenant.php` (prefixo `/customer/{slug}` — agendamento público e formulários públicos).
- `app/Http/Controllers/Tenant/PublicPatientController.php`.
- `app/Http/Controllers/Tenant/PublicPatientRegisterController.php`.
- `app/Http/Controllers/Tenant/PublicAppointmentController.php`.
- `app/Http/Controllers/Tenant/PublicFormController.php`.
- `resources/views/tenant/*` relacionados a área pública (caminhos exatos devem ser confirmados conforme controllers).
