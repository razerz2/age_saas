# Notifications — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `NotificationController`.

- O controller utiliza respostas JSON simples (`{'success': true}`) e não define mensagens de erro textuais nas partes inspecionadas.

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado no guard `tenant`.
- Confirmar se há notificações cadastradas na base do Tenant.
- Validar se `TenantNotificationService` está configurado corretamente e utiliza a conexão/tabela esperadas.
- Em caso de falha ao carregar notificações via `/notifications/json`, revisar logs e configurações de cache/auth.
