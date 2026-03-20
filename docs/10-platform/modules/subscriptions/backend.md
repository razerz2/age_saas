# Backend

- SubscriptionController, PlanChangeRequestController
- Models: Subscription, PlanChangeRequest

## Regra de cobranca para plano de teste

- assinaturas de plano `test` nao exigem metodo de pagamento na validacao.
- assinaturas de plano `test` nao integram com Asaas para cobranca.
- assinaturas de plano `test` nao devem gerar invoices automaticas.
- comandos financeiros (`invoices:generate`, `subscriptions:subscriptions-process`, `subscriptions:process-recovery`) devem ignorar planos `test`.

## Lembretes automaticos de trial comercial

- comando: `subscriptions:notify-trial-reminders`
- eventos suportados:
  - `trial.ends_in_7_days`
  - `trial.ends_in_3_days`
  - `trial.ends_today`
  - `trial.expired`
- canais:
  - email (template `notification_templates` com `scope=platform`, `channel=email`)
  - WhatsApp oficial (`WhatsAppOfficialMessageService::sendByKey`)
  - notificacao interna (`SystemNotificationService`)
- idempotencia:
  - tabela `trial_reminder_dispatches`
  - chave unica por `subscription_id + event_key + reference_date`
  - controle por canal via `channels_sent` para evitar duplicidade em reprocessamentos/retries
