# Business Hours — Permissions

## Guard e middlewares de grupo

Fontes: `routes/tenant.php`.

- Grupo autenticado do Tenant:
  - Guard: `tenant`.
  - Middlewares aplicados ao prefixo `/workspace/{slug}`:
    - `web`
    - `persist.tenant`
    - `tenant.from.guard`
    - `ensure.guard`
    - `tenant.auth`

Todas as rotas de `business-hours` estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `BusinessHourController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão (por role)

Fontes: `BusinessHourController`.

- Determinação do médico em `store`:
  - `role = doctor`: usa sempre o médico vinculado ao usuário autenticado.
  - `role = user`: usa `allowedDoctors()`; se houver apenas um médico permitido, assume esse; caso contrário, `doctor_id` deve vir na requisição.
  - `role = admin`: pode especificar `doctor_id` livremente.

- Outras ações (`index`, `edit`, `update`, `destroy`) dependem indiretamente do filtro `HasDoctorFilter` aplicado a consultas, garantindo que usuários vejam/editem apenas horários de médicos permitidos.
