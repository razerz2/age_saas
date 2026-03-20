# Overview

CRUD e acoes administrativas de tenants.

- Fonte: rotas/controllers/views atuais.

## Status comercial operacional

A Platform exibe a situacao comercial da tenant com base na elegibilidade real de acesso.

- Regra central: `Tenant::isEligibleForAccess()`
- Criterio de acesso: assinatura ativa + plano vinculado na assinatura
- `tenants.plan_id` e legado e nao libera acesso isoladamente
- Fonte oficial do plano comercial vigente: `Tenant::currentSubscriptionPlan()` / `Tenant::commercialPlan()`
- Prefill operacional de regularizacao usa `Tenant::preferredRegularizationPlanId()` (com fallback legado controlado)

Status exibidos no administrativo:

- `Apta para acesso`
- `Bloqueada comercialmente`
- motivo detalhado: `Sem assinatura` ou `Assinatura sem plano`

## Criacao incompleta guiada

A criacao de tenant continua permitindo `plan_id` nulo.

- o provisionamento tecnico (tenant, banco e credenciais) continua ocorrendo normalmente
- quando a tenant nasce sem elegibilidade comercial, o redirect segue para o detalhe da tenant com alerta forte
- mensagem operacional padrao:
  - `Tenant criada com sucesso, porem o acesso esta bloqueado ate que um plano e uma assinatura validos sejam definidos.`
- proximo passo esperado:
  - criar/ativar assinatura vinculada a plano para liberar acesso
