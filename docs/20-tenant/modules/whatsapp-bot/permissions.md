# Permissions

## Feature flag por plano

A funcionalidade depende da feature comercial:

- `whatsapp_bot`

Referências:

- `App\Services\Tenant\WhatsAppBotConfigService::FEATURE_NAME`
- middleware `feature:whatsapp_bot`
- `App\Services\FeatureAccessService`

## Onde a feature é validada

### Webhook inbound

Rota protegida por middleware:

- `POST /customer/{slug}/webhooks/whatsapp/bot/{provider}`
- middleware: `feature:whatsapp_bot`

Além disso, o processor valida novamente em runtime:

- `WhatsAppBotInboundMessageProcessor` (`hasFeature(...)`).

### Settings do bot

Rota de update protegida por middleware:

- `POST /workspace/{slug}/settings/whatsapp-bot`
- middleware: `module.access:settings` + `feature:whatsapp_bot`

Controller também aplica validação explícita:

- `SettingsController::hasWhatsAppBotFeature(...)`

## Regra de exibição da aba

A aba `bot-whatsapp` só aparece se a feature estiver disponível para o tenant:

- variável de view `$showWhatsAppBotTab` definida em `SettingsController@index`.

## Comportamento por estado

- Feature não ativa no plano:
  - acesso ao webhook/settings negado por middleware (403).
- Feature ativa, mas `whatsapp_bot.enabled=false`:
  - inbound é ignorado;
  - opcionalmente envia `whatsapp_bot.disabled_message` quando configurada.
- Feature ativa e bot habilitado:
  - processamento segue normalmente, condicionado a provider válido.