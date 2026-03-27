# Frontend

## Localização da UI

A configuração do bot está na aba **Bot de WhatsApp** em:

- `resources/views/tenant/settings/index.blade.php` (tab e render condicional)
- `resources/views/tenant/settings/tabs/bot-whatsapp.blade.php` (formulário)

Controller de suporte:

- `app/Http/Controllers/Tenant/SettingsController.php`

## Regra de exibição da aba

A aba só é exibida quando a feature de plano está ativa:

- `SettingsController::hasWhatsAppBotFeature(...)`
- variável de view: `$showWhatsAppBotTab`
- render condicional no `settings/index.blade.php`.

## Campos de configuração

Persistidos no namespace `whatsapp_bot.*` em `tenant_settings`.

Campos principais:

- `enabled`
- `provider_mode` (`shared_with_notifications` | `dedicated`)
- `provider` (quando `dedicated`)
- `welcome_message`
- `disabled_message` (mensagem opcional quando bot desativado)
- `allow_schedule`
- `allow_view_appointments`
- `allow_cancel_appointments`

Campos por provider dedicado:

- Meta/Oficial:
  - `bot_meta_access_token`
  - `bot_meta_phone_number_id`
  - `bot_meta_waba_id`
- Z-API:
  - `bot_zapi_api_url`
  - `bot_zapi_token`
  - `bot_zapi_client_token`
  - `bot_zapi_instance_id`
- WAHA:
  - `bot_waha_base_url`
  - `bot_waha_api_key`
  - `bot_waha_session`

## Comportamento dinâmico da tela

Implementado com Alpine (`x-data`, `x-show`):

- seleção de modo `shared_with_notifications` ou `dedicated`;
- exibição condicional dos campos de provider dedicado;
- resumo do provider efetivo herdado quando modo compartilhado.

## Validações de UX/backend refletidas na aba

- `welcome_message` é obrigatória quando `enabled=true`;
- `provider` e credenciais obrigatórias quando `provider_mode=dedicated`;
- mensagens de erro por campo exibidas na própria aba;
- mantém padrão visual de cards/form actions do Settings (incluindo dark mode).