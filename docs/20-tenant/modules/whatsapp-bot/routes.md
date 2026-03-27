# Routes

## Prefixos

- Entrada webhook (tenant público): `/customer/{slug}`
- Configurações autenticadas: `/workspace/{slug}` (grupo `tenant.*`)

## Webhook do bot

Arquivo: `routes/tenant.php`

- `POST /customer/{slug}/webhooks/whatsapp/bot/{provider}`
  - Name: `tenant.whatsapp-bot.webhook`
  - Controller: `Tenant\WhatsAppBotWebhookController@handle`
  - `provider` permitido: `whatsapp_business|meta|zapi|waha`

Middlewares aplicados no grupo e rota:

- grupo `tenant-web` (resolução de tenant por path);
- `feature:whatsapp_bot` na rota do webhook.

Observações:

- endpoint orientado a webhook (não requer autenticação de usuário do tenant);
- depende de `{slug}` para resolver tenant;
- o processamento do bot é stateless do ponto de vista de sessão web de usuário;
- `VerifyCsrfToken` tem exceção para `customer/*/webhooks/whatsapp/bot/*`.

## Rotas de settings do bot

Arquivo: `routes/tenant.php`

Dentro de `/workspace/{slug}`, no grupo `module.access:settings`:

- `GET /workspace/{slug}/settings`
  - Name: `tenant.settings.index`
  - Controller: `Tenant\SettingsController@index`
- `POST /workspace/{slug}/settings/whatsapp-bot`
  - Name: `tenant.settings.update.whatsapp-bot`
  - Controller: `Tenant\SettingsController@updateWhatsAppBot`
  - Middleware adicional: `feature:whatsapp_bot`