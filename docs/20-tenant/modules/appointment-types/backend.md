# Appointment Types — Backend

Fontes: `app/Http/Controllers/Tenant/AppointmentTypeController.php`.

## Controller

- `App\Http\Controllers\Tenant\AppointmentTypeController`
  - Métodos públicos:
    - `index(Request $request)`
    - `create()`
    - `store(StoreAppointmentTypeRequest $request)`
    - `show($slug, $id)`
    - `edit($slug, $id)`
    - `update(UpdateAppointmentTypeRequest $request, $slug, $id)`
    - `gridData(Request $request, $slug)`
    - `destroy($slug, $id)`

## Requests

- `App\Http\Requests\Tenant\StoreAppointmentTypeRequest`
- `App\Http\Requests\Tenant\UpdateAppointmentTypeRequest`

## Models

- `App\Models\Tenant\AppointmentType`
- `App\Models\Tenant\Doctor`

## Traits e helpers

- `App\Http\Controllers\Tenant\Concerns\HasDoctorFilter`
- Uso de `Auth::guard('tenant')` para identificar o usuário e aplicar permissões.

## Fluxo geral (factual)

- `index`:
  - Lista `AppointmentType` com `doctor.user`.
  - Aplica `applyDoctorFilter` (`HasDoctorFilter`).
  - Admin pode filtrar por `doctor_id` via query string.

- `create`:
  - Busca médicos ativos que ainda não possuem tipo de consulta, permitindo edição do médico quando necessário.

- `store`:
  - Determina o médico alvo com base em `role` (`admin`, `doctor`, `user`) e `doctor_id`.
  - Gera UUID (`Str::uuid()`) e cria o registro.

- `show` / `edit`:
  - Carregam `AppointmentType` com `doctor.user`.
  - `edit` lista médicos ativos que não possuem tipo ou o médico atual.

- `update`:
  - Valida permissão do usuário comum via `getAllowedDoctorIds()` antes de atualizar.

- `gridData`:
  - Retorna JSON para Grid.js com colunas nome, médico, duração, preço, cor e actions.

- `destroy`:
  - Verifica permissão (admin ou médico permitido) via `getAllowedDoctorIds()` e então exclui o registro.
