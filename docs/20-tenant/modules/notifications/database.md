# Notifications — Database

## Models

- `App/Models/Tenant/Notification.php`
  - Usado em `NotificationController`.
  - Campos/relacionamentos não são expostos diretamente no controller inspecionado.

- `App/Services/TenantNotificationService`
  - Usa o model `Notification` para contagem e marcação em massa.

## Tabelas / Migrations

- Tabela principal de notificações do Tenant:
  - Nome não explicitamente visto no controller.
  - (não identificado no código — provável em `database/migrations/tenant/*notifications*`).

## Relações relevantes

- As notificações são listadas e marcadas como lidas em contexto de Tenant, via `Notification` e `TenantNotificationService`.
