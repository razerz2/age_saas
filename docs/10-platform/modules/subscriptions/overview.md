# Overview

Ciclo de assinatura e mudanca de plano de tenants.

- Fonte: rotas/controllers/views atuais.

## Dominio

A **Assinatura** representa o vinculo comercial entre:

- `tenant` (quem consome)
- `plan` (qual catalogo/offer)
- status e vigencia (`starts_at`/`ends_at`)

O **Tenant** nao deve definir plano diretamente sem uma assinatura (legado: ainda existe `tenants.plan_id` para compatibilidade).

Fonte oficial para leitura do plano comercial vigente:

- `Tenant::currentSubscriptionPlan()`
- `Tenant::commercialPlan()`

## Leitura operacional

A Platform mostra se uma assinatura efetivamente libera acesso da tenant.

- regra de acesso continua centralizada em `Tenant::isEligibleForAccess()`
- uma assinatura so libera acesso quando for a assinatura ativa da tenant e possuir plano valido

## Regra financeira por tipo de plano

- `plan_type = real`: fluxo financeiro normal (metodo de pagamento, Asaas, invoice e cobranca).
- `plan_type = test`: sem fluxo financeiro.
- para plano de teste, o sistema nao deve exigir metodo de pagamento no fluxo publico/administrativo e nao deve gerar invoice/cobranca.

## Trial comercial (landing)

Assinaturas de trial comercial (onboarding publico) usam os campos:

- `is_trial = true`
- `trial_ends_at` preenchido
- `auto_renew = false`

Comportamento:

- libera acesso enquanto o trial estiver valido;
- nao gera invoice;
- nao aciona cobranca/Asaas;
- ao expirar, deixa de liberar acesso e tenant volta ao bloqueio comercial ate assinatura valida.

## Conversao de trial para plano pago

Fluxo operacional (manual):

- a Platform pode abrir `Platform.subscriptions.create` com `origin=trial` e `conversion_from_trial=1`;
- nesse contexto, a selecao de plano permite apenas planos `real` ativos;
- ao criar a nova assinatura paga, qualquer trial aberto da tenant e encerrado automaticamente;
- o acesso da tenant so volta ao fluxo completo quando houver assinatura comercial valida para `Tenant::isEligibleForAccess()`.
