# Public Customer — Permissions

## Middleware e contexto

Fontes: `routes/tenant.php`.

- Grupo público do Tenant:
  - Prefixo: `/customer/{slug}`.
  - Middleware: `tenant-web`.

As rotas de:
- identificação/cadastro de paciente,
- criação/visualização de agendamento público,
- APIs de agendamento,
- formulários públicos,

estão dentro desse grupo.

## Guards

- As rotas públicas não utilizam o guard `tenant` autenticado; são acessíveis sem login convencional.

## module.access

- Não há uso de `module.access:<modulo>` nas rotas públicas (`/customer/{slug}`) em `routes/tenant.php`.
- (não identificado no código).

## Observações

- A proteção dessas rotas é feita via `tenant-web` (resolução de Tenant e contexto de URL).
- Políticas adicionais de limite de acesso/abuso podem existir em outros middlewares (não identificados diretamente aqui).
