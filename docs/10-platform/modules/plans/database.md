# Database

- `plans` (platform)
  - Campos principais: nome, periodicidade, preco, categoria, features, ativo.
  - Campos de dominio:
    - `plan_type` (`real`/`test`) (default: `real`)
    - `show_on_landing_page` (boolean) (default: `true`)
    - `trial_enabled` (boolean) (default: `false`)
    - `trial_days` (integer nullable)
  - Regras de acesso por feature ficam em `plan_access_rules` e tabelas relacionadas.
