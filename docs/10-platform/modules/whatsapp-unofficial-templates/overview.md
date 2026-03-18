# Overview

Modulo da Platform para manter templates internos de WhatsApp nao oficial.

Boundary de dominio:

- `whatsapp-official-templates`: templates oficiais da Meta (Cloud API);
- `whatsapp-unofficial-templates`: templates internos da Platform para runtime nao oficial;
- `tenant-default-notification-templates`: baseline operacional padrao por tenant (nao oficial).

Campos principais:

- `key`
- `title`
- `category`
- `body`
- `variables`
- `is_active`

Estes templates nao dependem de aprovacao externa.

Keys baseline atuais:

- `invoice.created`
- `invoice.upcoming_due`
- `invoice.overdue`
- `tenant.suspended_due_to_overdue`
- `tenant.welcome`
- `subscription.created`
- `subscription.recovery_started`
- `credentials.resent`
- `security.2fa_code`

Padrao de variaveis Platform (sem aliases):

- `customer_name`
- `tenant_name`
- `invoice_amount`
- `due_date`
- `payment_link`
- `plan_name`
- `plan_amount`
- `login_url`
- `delivery_channel`
- `code`
- `expires_in_minutes`

Papel deste catalogo na resolucao:

- este modulo e a fonte Platform para fallback nao oficial quando habilitado explicitamente;
- o runtime tenant nao usa fallback Platform automatico por padrao.

Fluxo canonico de envio nao oficial (integrado):

1. resolver por key (`WhatsAppUnofficialTemplateResolutionService`) dentro do `NotificationDispatcher`
2. renderizacao com `TemplateRenderer`
3. envio de texto final pelo `WhatsAppSender`
4. traducao provider-specific em `WhatsAppService` (WAHA/Z-API)

Resultado:

- template permanece agnostico a provider;
- provider recebe apenas mensagem final renderizada.

Teste manual operacional:

- na tela de detalhe do template interno da Platform ha modal de teste manual;
- permite:
  - preencher variaveis manualmente;
  - preencher variaveis com dados ficticios;
  - visualizar preview renderizado antes do envio;
  - enviar para telefone de teste usando provider nao oficial ativo.

Diferenca para modulo Oficial:

- `whatsapp-official-templates` depende de template Meta aprovado;
- `whatsapp-unofficial-templates` nao depende de cadastro/aprovacao Meta.
