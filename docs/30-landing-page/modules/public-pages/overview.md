# Overview

Paginas institucionais e comerciais da landing.

## Regra de exibicao publica de planos

A listagem publica de planos (home, pagina `/planos` e endpoint `/planos/json/{id}`) mostra somente planos elegiveis para comercializacao publica.

Criterio centralizado no model `Plan`:

- `category = commercial`
- `is_active = true`
- `plan_type = real`
- `show_on_landing_page = true`

Diferencas:

- `plan_type = test`: plano de teste interno, nao aparece publicamente.
- `plan_type = real`: plano comercializavel em producao (ainda depende de visibilidade e status ativo).
- `show_on_landing_page = true`: plano visivel na landing.
- `show_on_landing_page = false`: plano oculto na landing.

## CTA de trial comercial

Quando o plano elegivel possui trial comercial habilitado (`trial_enabled = true` e `trial_days > 0`), a landing exibe CTA de teste gratis.

Fluxo do CTA:

- direciona para `/planos?trial=1&plan_id={id}`;
- abre o modal de pre-cadastro com modo trial;
- envia `trial=1` no POST de pre-cadastro.
