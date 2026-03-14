# Backend

Principais classes:

- `App\Http\Controllers\Platform\WhatsAppOfficialTemplateController`
- `App\Http\Requests\Platform\StoreWhatsAppOfficialTemplateRequest`
- `App\Http\Requests\Platform\UpdateWhatsAppOfficialTemplateRequest`
- `App\Models\Platform\WhatsAppOfficialTemplate`
- `App\Services\Platform\WhatsAppOfficialTemplateService`
- `App\Services\Platform\WhatsAppOfficialTemplateResolver`
- `App\Services\Platform\WhatsAppOfficialMessageService`
- `App\Services\WhatsApp\MetaCloudTemplateApiService`
- `App\Support\WhatsAppOfficialTemplateValidator`
- `App\Http\Middleware\EnsureWhatsAppOfficialProvider`
- `App\Policies\Platform\WhatsAppOfficialTemplatePolicy`

Fluxo de versionamento:

- `draft`: pode editar;
- `approved`: nao edita direto, cria nova versao `draft`;
- demais status: bloqueio de edicao estrutural;
- acao de duplicar cria nova versao.

Integracao Meta:

- endpoint base normalizado para Graph API `v22.0`;
- criacao em `/{WABA_ID}/message_templates`;
- sincronizacao por `name + language` no mesmo recurso;
- logs tecnicos sem exposicao de token completo.
- categorias:
  - `UTILITY` -> enviada como `UTILITY`;
  - `SECURITY` -> enviada como `AUTHENTICATION` na Meta.

Variaveis padronizadas no baseline SaaS:

- `customer_name`
- `tenant_name`
- `invoice_amount`
- `due_date`
- `payment_link`
- `code`

Relacao com eventos reais da Platform:

- `invoice.created`:
  - fluxo de criacao em `Platform\\InvoiceController@store`
- `invoice.upcoming_due`:
  - rotina `invoices:notify-upcoming` (`NotifyUpcomingInvoicesCommand`)
- `invoice.overdue`:
  - rotina `invoices:invoices-check-overdue` (`CheckOverdueInvoices`)
- `tenant.suspended_due_to_overdue`:
  - suspensao aplicada no mesmo `CheckOverdueInvoices`
- `security.2fa_code`:
  - envio de codigo em `TwoFactorController` + `TwoFactorCodeOfficialNotification`
- `tenant.welcome`:
  - onboarding em `PreTenantProcessorService::sendWelcomeEmail`
- `subscription.created`:
  - criacao de assinatura em `PreTenantProcessorService` e `SubscriptionController`
- `subscription.recovery_started`:
  - rotina `subscriptions:process-recovery` (`ProcessRecoverySubscriptionsCommand`)
- `credentials.resent`:
  - reenvio de credenciais em `TenantController::sendCredentials`

Consumo runtime do catalogo oficial:

- os fluxos Platform usam `WhatsAppOfficialMessageService::sendByKey(...)`;
- lookup sempre por `key` + `provider=whatsapp_business` + `status=approved` (via `WhatsAppOfficialTemplateResolver`);
- payload para Meta e enviado como `type=template` (Graph API `/{PHONE_NUMBER_ID}/messages`);
- parametros do corpo sao montados na ordem definida em `variables` do template oficial.

## Teste manual (test-send)

Endpoint:

- `POST /Platform/whatsapp-official-templates/{whatsappOfficialTemplate}/test-send`

### Fluxo de envio de teste

Fluxo da interface:

- na tela Show, o usuario abre a modal de teste manual;
- informa numero de destino e valores das variaveis;
- o backend monta o payload com base no template selecionado e envia pela Meta Cloud API.

Fluxo tecnico:

- controller valida entrada (`phone`, `variables`) e aplica policy `testSend`;
- o envio e executado por `WhatsAppOfficialMessageService::sendManualTest(...)`;
- o provider usado e `WhatsAppBusinessProvider` (Graph API `/{PHONE_NUMBER_ID}/messages`).

### Diferenca entre schema local e schema remoto

O cadastro local do template (campos `body_text`, `variables`, `buttons`) pode divergir do template remoto aprovado na Meta.

No teste manual, quando houver divergencia e o template remoto estiver disponivel em `meta_response`:

- o envio e montado para respeitar o schema remoto aprovado (ex.: placeholders reais no `BODY` e botoes dinamicos em `BUTTONS`);
- o schema local continua sendo exibido na tela, mas nao e considerado fonte de verdade para a quantidade de parametros do envio quando o remoto divergir.

### Tratamento de parametros (BODY/BUTTONS)

`UTILITY`:

- parametros do `BODY` seguem a ordem definida em `variables` do template local (placeholders `1`, `2`, `3`...).

`AUTHENTICATION`/`SECURITY`:

- parametros do `BODY` sao resolvidos dinamicamente pela quantidade de placeholders do `BODY` remoto salvo em `meta_response`;
- se o schema remoto possuir `BUTTONS` dinamicos (ex.: URL com placeholder), o envio inclui tambem os componentes `button` com:
  - `index` e `sub_type` do botao remoto;
  - quantidade exata de parametros exigidos pela Meta.

### Regras de bloqueio

- apenas templates `APPROVED` sao enviados no teste manual;
- provider ativo deve ser `whatsapp_business`;
- numero de destino deve ser valido;
- se o schema remoto exigir parametros e nao houver valores suficientes, o backend retorna erro e nao dispara envio.

### Logs tecnicos (teste manual)

Evento: `platform_whatsapp_official_manual_test`.

Campos registrados:

- `template_key`
- `template_name`
- `destination_masked`
- `category`
- `remote_body_params_expected`
- `remote_button_params_expected`
- `button_components_count`
- `result`
- `meta_error_code`
- `meta_error_details`

Observacao: alem do evento acima, o provider registra tentativas/resultados de envio (`WhatsApp Meta template send attempt` / `WhatsApp Meta authentication template send attempt`) com contagem de parametros e componentes.

Regras importantes (resumo):

- o envio de teste nao utiliza texto livre; sempre usa o template oficial selecionado;
- o envio nao altera o runtime do Tenant.

Fallback seguro:

- se template nao existir, estiver sem aprovacao ou provider ativo nao for `whatsapp_business`, o envio e abortado;
- se variaveis obrigatorias estiverem ausentes, o envio e abortado;
- em todos os cenarios acima, o sistema registra log estruturado de motivo (`platform_whatsapp_official_send_skipped` / `platform_whatsapp_official_send_failed`) com `key`, `reason`, `provider`, `latest_status` e `latest_version` quando aplicavel;
- nao ha fallback silencioso para mensagem hardcoded nos fluxos integrados ao catalogo oficial.

## sample_variables (exemplos obrigatorios)

A Meta rejeita templates com placeholders (`{{1}}`, `{{2}}`, ...) quando nao ha "texto de amostra" (examples) para as variaveis.

No modulo, os exemplos sao persistidos em `sample_variables` (JSON), mapeando placeholder -> exemplo:

```json
{ "1": "Rafael", "2": "14/03/2026 as 09:00", "3": "https://..." }
```

Validacao aplicada antes de submissao:

- todo placeholder presente em `body_text` deve ter um exemplo correspondente em `sample_variables`;
- payload de criacao/submissao para a Meta inclui `BODY.example.body_text` com os exemplos na ordem dos placeholders.
