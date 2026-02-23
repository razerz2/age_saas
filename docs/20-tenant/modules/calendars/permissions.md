# Calendars — Permissions

## Guard e middlewares de grupo

Fontes: `routes/tenant.php`.

- Grupo autenticado do Tenant:
  - Guard: `tenant` (via middlewares de autenticação do grupo).
  - Middlewares aplicados ao prefixo `/workspace/{slug}`:
    - `web`
    - `persist.tenant`
    - `tenant.from.guard`
    - `ensure.guard`
    - `tenant.auth`

Todas as rotas de `calendars` estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `CalendarController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão (por role)

Fontes: `CalendarController`.

- `role = admin`
  - Pode ver/editar/remover todos os calendários.
- `role = doctor`
  - Pode criar calendário apenas para si mesmo.
  - Não pode criar mais de um calendário.
  - Só pode visualizar/editar/remover calendários cujo `doctor_id` seja o seu.
- `role = user`
  - Acesso restrito a médicos retornados por `allowedDoctors()`.
  - Só vê/edita/remover calendários de médicos que possui permissão.
