# Overview

Modulo da Platform para manter o baseline padrao de templates operacionais usados pelos tenants.

Boundary de dominio:

- `whatsapp-official-templates`: catalogo oficial global da Meta (inclui eventos SaaS da Platform e baseline clinico oficial do Tenant);
- `tenant-default-notification-templates`: eventos clinicos operacionais do Tenant (dominio WhatsApp Nao Oficial).

Navegacao Platform:

- `WhatsApp Oficial`:
  - Templates oficiais Meta (`whatsapp-official-templates`)
  - Templates oficiais Tenant (`whatsapp-official-tenant-templates`)
- `WhatsApp Nao Oficial`:
  - Templates internos da Platform (`whatsapp-unofficial-templates`)
  - Templates padrao Tenant (`tenant-default-notification-templates`)

Keys baseline atuais:

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

Padrao de variaveis Tenant (sem aliases novos):

- `patient.name`
- `clinic.name`
- `appointment.date`
- `appointment.time`
- `professional.name`
- `appointment.mode`
- `links.appointment_confirm`
- `links.appointment_cancel`
- `links.appointment_details`
- `links.waitlist_offer`
- `waitlist.offer_expires_at`

Provisionamento:

- no provisionamento de tenant, os templates ativos deste modulo sao copiados para `tenant.notification_templates` de forma idempotente;
- comportamento padrao: somente ausentes (sem sobrescrever existentes);
- runtime Tenant continua o mesmo (`config/notification_templates.php` + tabela tenant `notification_templates`).

Estrategia de resolucao nao oficial (oficial para esta etapa):

1. `tenant.notification_templates` (inclui baseline copiado + customizacoes da tenant)
2. fallback para `whatsapp_unofficial_templates` da Platform apenas quando solicitado de forma explicita e segura

Observacao:

- fallback Platform nao e automatico no runtime atual para evitar ambiguidade funcional.

Fluxo canonico integrado no runtime nao oficial:

1. lookup por key em `tenant.notification_templates` (baseline copiado + customizacoes)
2. fallback Platform opcional (`whatsapp_unofficial_templates`) apenas com opt-in explicito
3. renderizacao de placeholders via `TemplateRenderer`
4. envio provider-agnostico via `WhatsAppSender` -> `WhatsAppService` (WAHA/Z-API)

Limite desta etapa:

- integracao prioriza o caminho central baseado em templates;
- hardcodes isolados em fluxos legados permanecem para migracao incremental posterior.

Teste manual:

- preview e envio manual foram implementados no modulo de templates internos da Platform (`whatsapp-unofficial-templates`);
- este modulo segue com foco em baseline tenant, provisionamento e governanca de conteudo padrao.
