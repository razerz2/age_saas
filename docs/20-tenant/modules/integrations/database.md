# Integrations — Database

## Models

- `App/Models/Tenant/Integrations.php`
  - Usado em `IntegrationController`.
  - Campos/relacionamentos não são expostos diretamente no controller inspecionado.

## Tabelas / Migrations

- Tabela principal de integrações do Tenant:
  - Nome não explicitamente visto no controller.
  - (não identificado no código — provável em `database/migrations/tenant/*integrations*`).

## Relações relevantes

- Cada registro em `integrations` representa uma configuração de integração do Tenant.
