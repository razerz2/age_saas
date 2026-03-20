# Tenant - Visao Geral

A **Tenant** e a area especifica de cada clinica dentro do sistema.

## Objetivos da area

- Gerenciar pacientes, medicos e agendas.
- Realizar e acompanhar agendamentos (presenciais e online).
- Controlar formularios personalizados e respostas.
- Visualizar relatorios operacionais e, opcionalmente, financeiros.

## Elegibilidade comercial para acesso

O acesso ao ambiente autenticado da tenant depende da elegibilidade comercial centralizada em `Tenant::isEligibleForAccess()`.

Criterios atuais:

- possuir assinatura ativa;
- a assinatura ativa possuir plano vinculado;
- ou possuir assinatura de trial comercial valida (nao expirada).

Observacoes:

- `tenants.plan_id` isoladamente **nao** e criterio suficiente para liberar acesso.
- tenant criada sem assinatura/plano validos pode existir no sistema, mas fica com acesso bloqueado ate regularizacao comercial.

Fluxo esperado:

1. tenant e criada;
2. assinatura e vinculada e ativada com plano;
3. somente apos elegibilidade comercial o acesso ao `/workspace/{slug}` e liberado.

## Bloqueio controlado para trial expirado

Quando o trial comercial expira:

- a sessao do usuario tenant nao e derrubada automaticamente;
- o acesso fica limitado ao dashboard reduzido e telas de regularizacao/upgrade;
- rotas principais de operacao permanecem bloqueadas ate assinatura comercial valida.

Para uma visao funcional detalhada do Tenant, veja `TENANT.md` na raiz do projeto.
