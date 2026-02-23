# Appointment Types — Permissions

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

Todas as rotas de `appointment-types` estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `AppointmentTypeController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão (por role)

Fontes: `AppointmentTypeController`.

- `store` e determinação de médico:
  - `role = doctor`: usa sempre o médico vinculado ao usuário autenticado.
  - `role = user`: usa `allowedDoctors()`; se houver apenas um médico permitido, assume esse; caso contrário, `doctor_id` deve vir na requisição.
  - `role = admin`: pode especificar `doctor_id` livremente.

- `update` e `destroy`:
  - Validam que o usuário é admin ou que o `doctor_id` do tipo de atendimento está na lista de médicos permitidos (`getAllowedDoctorIds()`), abortando com 403 em caso contrário.
