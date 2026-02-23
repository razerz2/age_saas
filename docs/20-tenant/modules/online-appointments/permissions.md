# Online Appointments — Permissions

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

Todas as rotas de `online-appointments` estão dentro desse grupo.

## module.access

- Em `routes/tenant.php`, o grupo `appointments/online` está protegido por:
  - `middleware(['module.access:online_appointments'])`.
- Ou seja, o acesso ao módulo exige o módulo `online_appointments` habilitado para o usuário.

## Regras internas de permissão

- O controller não aplica verificações adicionais de `role` diretamente, além da checagem do modo de agendamento (`TenantSetting`).
- A autorização fina (quais usuários podem usar o módulo) é tratada via middleware `module.access:online_appointments`.
