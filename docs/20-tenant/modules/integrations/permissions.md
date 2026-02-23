# Integrations — Permissions

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

Todas as rotas de `integrations` estão dentro desse grupo.

## module.access

- Não há `module.access:<modulo>` explícito envolvendo `IntegrationController` em `routes/tenant.php`.
- (não identificado no código).

## Regras internas de permissão

- O controller não aplica verificações adicionais de `role` diretamente no código inspecionado.
- Regras de acesso podem ser controladas por middlewares globais de Tenant e políticas (não identificadas diretamente aqui).
