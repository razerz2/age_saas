# Notifications — Permissions

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

Todas as rotas de `notifications` estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `NotificationController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão

- O controller não aplica regras adicionais de role/permissão no código inspecionado.
- Regras de acesso adicionais podem ser definidas em middlewares globais de Tenant (não identificadas diretamente aqui).
