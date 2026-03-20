# Overview

Fluxo publico de pre-cadastro e webhook associado.

## Validacao publica de plano

No pre-cadastro publico, o `plan_id` informado so e aceito quando o plano for elegivel para comercializacao publica (`Plan::publiclyAvailable()`).

Bloqueios aplicados no fluxo publico:

- plano `test`;
- plano oculto (`show_on_landing_page = false`);
- plano inativo (`is_active = false`);
- plano fora da categoria comercial.

## Trial comercial (testar gratis)

Quando o pre-cadastro recebe `trial=1` com `plan_id` valido, o fluxo cria onboarding sem etapa financeira, desde que o plano seja comercial e habilitado para trial:

- `plan_type = real`
- `is_active = true`
- `trial_enabled = true`
- `trial_days > 0`

Resultado esperado:

- tenant provisionada normalmente;
- assinatura criada com `is_trial = true` e `trial_ends_at`;
- sem invoice, sem cobranca e sem chamada Asaas;
- retorno com `redirect_url` para login da tenant.
