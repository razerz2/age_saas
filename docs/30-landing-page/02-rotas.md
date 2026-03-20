# Landing Page - Rotas (Resumo)

Este arquivo resume a organizacao das rotas publicas relacionadas a landing page.

## Organizacao geral

- Rotas publicas sem autenticacao, normalmente na raiz do dominio.

Rotas principais de planos:

- `GET /` (landing principal)
- `GET /planos` (pagina de planos)
- `GET /planos/json/{id}` (detalhe do plano para modal)
- `POST /pre-cadastro` (contratacao/pre-cadastro publico)

Fluxo de trial comercial na landing:

- `GET /planos?trial=1&plan_id={id}` (pre-selecao de plano para teste gratis)
- `POST /pre-cadastro` com `trial=1` (quando elegivel)

## Regra publica de planos

As rotas publicas de planos usam o criterio centralizado `Plan::publiclyAvailable()`.

Somente planos:

- comerciais;
- ativos;
- `plan_type = real`;
- `show_on_landing_page = true`.

`plan_id` nao elegivel informado manualmente no fluxo publico de pre-cadastro e rejeitado.

No modo trial, alem da elegibilidade publica normal, o plano precisa ter trial habilitado:

- `trial_enabled = true`
- `trial_days > 0`

## Referencia detalhada

- Rotas e comportamentos atuais de landing estao descritos em `ARQUITETURA.md` e `PLATFORM.md` nas secoes de rotas publicas.
