# Overview

Gestao de planos e regras de acesso por feature.

- Fonte: rotas/controllers/views atuais.

## Categorizacao de Planos

O **Plano** representa o catalogo (oferta) e nao o vinculo comercial.

- `plan_type`
  - `real` (label: `Producao`) -> plano comercial/normal
  - `test` (label: `Teste`) -> plano interno para testes/operacao
- `show_on_landing_page` (boolean)
  - `true` (label: `Visivel na Landing`)
  - `false` (label: `Oculto na Landing`)
- trial comercial
  - `trial_enabled` + `trial_days`
  - somente para plano `real` ativo
  - plano `test` nao participa de trial comercial publico

A Platform exibe esses labels de forma amigavel nas telas de planos e assinaturas.
