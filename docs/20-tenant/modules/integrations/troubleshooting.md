# Integrations — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `IntegrationController`.

- O controller utiliza principalmente mensagens de sucesso (`with('success', ...)`) e não define mensagens de erro específicas nas partes inspecionadas.

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado no guard `tenant`.
- Confirmar se as migrations de `integrations` foram executadas (`database/migrations/tenant/*integrations*`).
- Validar se os dados enviados em create/update atendem às regras de `StoreIntegrationRequest`/`UpdateIntegrationRequest`.
- Revisar logs de aplicação para exceções relacionadas a integrações.
