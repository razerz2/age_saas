# Business Hours — Backend

Fontes: `app/Http/Controllers/Tenant/BusinessHourController.php`.

## Controller

- `App\Http\Controllers\Tenant\BusinessHourController`
  - Métodos públicos:
    - `gridData(Request $request, $slug)`
    - `index()`
    - `create()`
    - `store(StoreBusinessHourRequest $request)`
    - `show($slug, $id)`
    - `edit($slug, $id)`
    - `update(UpdateBusinessHourRequest $request, $slug, $id)`
    - `destroy($slug, $id)`

## Requests

- `App\Http\Requests\Tenant\StoreBusinessHourRequest`
- `App\Http\Requests\Tenant\UpdateBusinessHourRequest`

## Models

- `App\Models\Tenant\BusinessHour`
- `App\Models\Tenant\Doctor`

## Traits e helpers

- `App\Http\Controllers\Tenant\Concerns\HasDoctorFilter`
- Uso de `Auth::guard('tenant')` para obter o usuário atual.

## Fluxo geral (factual)

- `gridData`:
  - Carrega `BusinessHour` com `doctor.user`.
  - Aplica `applyDoctorFilter` para respeitar permissões por médico.
  - Implementa busca por `weekday` e nome do médico.
  - Retorna JSON paginado para Grid.js, renderizando `tenant.business-hours.partials.actions`.

- `index`:
  - Lista horários de atendimento com `doctor.user`, aplicando filtro de médico e ordenando por dia/hora.

- `create`:
  - Lista médicos (via `Doctor::with('user')`) filtrados por `HasDoctorFilter`.

- `store`:
  - Determina o médico com base em `role` (`admin`, `doctor`, `user`) e `doctor_id` da requisição.
  - Cria múltiplos registros de `BusinessHour` para dias selecionados (`weekdays`) evitando duplicatas.
  - Usa UUID para `id`.

- `show`:
  - Carrega `BusinessHour` com `doctor.user` e envia para `tenant.business-hours.show`.

- `edit`:
  - Carrega `BusinessHour` com `doctor`.
  - Lista médicos permitidos para edição.

- `update`:
  - Atualiza `BusinessHour` com dados validados.

- `destroy`:
  - Remove o registro e redireciona com mensagem de sucesso.
