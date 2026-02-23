# Calendar Sync — Permissions

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

Todas as rotas de `calendar-sync` estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `CalendarSyncStateController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão

- O controller não aplica regras adicionais de autorização explícitas no código inspecionado.
- Recomenda-se validar permissões de acesso a agendamentos associados em camadas superiores (não identificado diretamente no controller).
