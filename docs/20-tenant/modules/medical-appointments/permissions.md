# Medical Appointments — Permissions

## Guard e middlewares de grupo

Fontes: `routes/tenant.php` e `MedicalAppointmentController`.

- Grupo autenticado do Tenant:
  - Guard: `tenant`.
  - Middlewares aplicados ao prefixo `/workspace/{slug}`:
    - `web`
    - `persist.tenant`
    - `tenant.from.guard`
    - `ensure.guard`
    - `tenant.auth`

Todas as rotas de atendimento médico (`/workspace/{slug}/atendimento/*`) estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `MedicalAppointmentController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão (por role)

Fontes: `MedicalAppointmentController`.

- `role = admin`:
  - Pode ver todos os agendamentos na sessão do dia.
  - Pode filtrar por médicos específicos via `doctor_ids`.

- `role = doctor`:
  - Sempre filtrado para seus próprios agendamentos (médico associado ao usuário).

- `role = user`:
  - Filtrado por médicos associados via `allowedDoctors()`.
  - Tentativas de usar médicos sem permissão são bloqueadas (403) e registradas em log.

Essas regras são aplicadas tanto na construção da sessão (`session`) quanto em operações que dependem de `checkPermission`.
