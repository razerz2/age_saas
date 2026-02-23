# Calendars — Backend

Fontes: `app/Http/Controllers/Tenant/CalendarController.php`.

## Controller

- `App\Http\Controllers\Tenant\CalendarController`
  - Métodos públicos:
    - `gridData(Request $request, $slug)`
    - `index()`
    - `create()`
    - `store(StoreCalendarRequest $request)`
    - `show($slug, $id)`
    - `edit($slug, $id)`
    - `update(UpdateCalendarRequest $request, $slug, $id)`
    - `destroy($slug, $id)`
    - `eventsRedirect()`

## Requests

- `App\Http\Requests\Tenant\StoreCalendarRequest`
- `App\Http\Requests\Tenant\UpdateCalendarRequest`

## Models

- `App\Models\Tenant\Calendar`
- `App\Models\Tenant\Doctor`

## Traits e helpers

- `App\Http\Controllers\Tenant\Concerns\HasDoctorFilter`
- Uso de `Auth::guard('tenant')` para identificar o usuário atual.

## Fluxo geral

- `index` carrega calendários com `doctor.user` e aplica filtro de médico (`HasDoctorFilter`).
- `create` impede múltiplos calendários para o mesmo médico e verifica restrições para `role=doctor`.
- `store` valida dados, gera UUID e cria calendário; trata casos de médico já possuir calendário.
- `show`, `edit`, `update`, `destroy` aplicam verificações de permissão baseadas em `role` e relação do usuário com o médico.
- `eventsRedirect` redireciona o usuário para a agenda apropriada ou para a lista de calendários.

> Todas as regras acima são extraídas diretamente do código do controller.
