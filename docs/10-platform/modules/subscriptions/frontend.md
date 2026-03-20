# Frontend

- Blade TailAdmin

## Visibilidade operacional

As telas de assinaturas exibem de forma explicita:

- tenant vinculada
- plano e seus indicadores (`Producao`/`Teste`, `Visivel`/`Oculto na Landing`)
- status e vigencia
- indicador se a assinatura libera acesso da tenant

## Entrada por regularizacao da tenant

A tela de criacao de assinatura pode ser aberta com `tenant_id` e `plan_id` por querystring para acelerar regularizacao comercial.

- quando aberta a partir da tela de tenant bloqueada, a tenant ja vem preselecionada
- o operador recebe contexto de que a acao desbloqueia o acesso comercial
