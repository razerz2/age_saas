# Calendar Sync — Backend

Fontes: `app/Http/Controllers/Tenant/CalendarSyncStateController.php`.

## Controller

- `App\Http\Controllers\Tenant\CalendarSyncStateController`
  - Métodos públicos:
    - `index()`
    - `create()`
    - `store(StoreCalendarSyncStateRequest $request)`
    - `show($slug, $id)`
    - `edit($slug, $id)`
    - `update(UpdateCalendarSyncStateRequest $request, $slug, $id)`
    - `destroy($slug, $id)`

## Requests

- `App\Http\Requests\Tenant\CalendarSync\StoreCalendarSyncStateRequest`
- `App\Http\Requests\Tenant\CalendarSync\UpdateCalendarSyncStateRequest`

## Models

- `App\Models\Tenant\CalendarSyncState`
- `App\Models\Tenant\Appointment`

## Fluxo geral (factual)

- `index`:
  - Lista `CalendarSyncState` com `appointment.patient` e `appointment.calendar.doctor.user`, ordenando por `last_sync_at` desc.

- `create`:
  - Carrega `Appointment` com `patient` e `calendar.doctor` para seleção ao criar um estado de sync.

- `store`:
  - Valida dados com `StoreCalendarSyncStateRequest`.
  - Gera UUID (`Str::uuid()`) e cria registro em `CalendarSyncState`.

- `show`:
  - Carrega um `CalendarSyncState` por id com relacionamentos (`appointment.patient`, `appointment.calendar.doctor.user`) e envia para view `tenant.calendar-sync.show`.

- `edit`:
  - Carrega `CalendarSyncState` com `appointment` e lista agendamentos para re-associar o estado de sync.

- `update`:
  - Atualiza `CalendarSyncState` com dados validados.

- `destroy`:
  - Remove registro e redireciona com mensagem de sucesso.
