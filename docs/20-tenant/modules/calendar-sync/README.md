# Módulo: Calendar Sync

Mapa rápido do módulo de estado de sincronização de calendário (Calendar Sync) do Tenant.

## Arquivos deste módulo

- `overview.md` — visão geral do módulo.
- `routes.md` — rotas autenticadas relacionadas ao estado de sincronização de calendário.
- `views.md` — telas e partials Blade do módulo.
- `backend.md` — controllers, requests, models e traits usados.
- `frontend.md` — JS/CSS associados e padrões de UI.
- `database.md` — modelos e tabelas relacionadas.
- `permissions.md` — guard/middlewares/regras de acesso.
- `troubleshooting.md` — problemas comuns e checklist de diagnóstico.

## Fontes consultadas (paths)

- `routes/tenant.php` (resource `calendar-sync`).
- `app/Http/Controllers/Tenant/CalendarSyncStateController.php`.
- `app/Http/Requests/Tenant/CalendarSync/StoreCalendarSyncStateRequest.php`.
- `app/Http/Requests/Tenant/CalendarSync/UpdateCalendarSyncStateRequest.php`.
- `app/Models/Tenant/CalendarSyncState.php`.
- `app/Models/Tenant/Appointment.php`.
- `resources/views/tenant/calendar-sync/*.blade.php`.
