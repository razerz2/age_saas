# Overview

Modulo da Platform para manter o baseline padrao de templates operacionais usados pelos tenants.

Boundary de dominio:

- `whatsapp-official-templates`: eventos SaaS da Platform (fatura, assinatura, seguranca etc.);
- `tenant-default-notification-templates`: eventos clinicos operacionais do Tenant.

Keys baseline atuais:

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

Provisionamento:

- no provisionamento de tenant, os templates ativos deste modulo sao copiados para `tenant.notification_templates` de forma idempotente;
- runtime Tenant continua o mesmo (`config/notification_templates.php` + tabela tenant `notification_templates`).

