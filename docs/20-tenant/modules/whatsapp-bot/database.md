# Database

## Configurações do bot (tenant_settings)

As configurações do módulo ficam em `tenant_settings` com prefixo `whatsapp_bot.`.

Chaves usadas atualmente:

- `whatsapp_bot.enabled`
- `whatsapp_bot.provider_mode`
- `whatsapp_bot.provider`
- `whatsapp_bot.welcome_message`
- `whatsapp_bot.disabled_message`
- `whatsapp_bot.allow_schedule`
- `whatsapp_bot.allow_view_appointments`
- `whatsapp_bot.allow_cancel_appointments`
- `whatsapp_bot.meta.access_token`
- `whatsapp_bot.meta.phone_number_id`
- `whatsapp_bot.meta.waba_id`
- `whatsapp_bot.zapi.api_url`
- `whatsapp_bot.zapi.token`
- `whatsapp_bot.zapi.client_token`
- `whatsapp_bot.zapi.instance_id`
- `whatsapp_bot.waha.base_url`
- `whatsapp_bot.waha.api_key`
- `whatsapp_bot.waha.session`

Leitura central:

- `App\Models\Tenant\TenantSetting::whatsappBotProvider()`

## Sessão conversacional do bot

Migration:

- `database/migrations/tenant/2026_03_27_000300_create_whatsapp_bot_sessions_table.php`

Model:

- `app/Models/Tenant/WhatsAppBotSession.php`

Tabela: `whatsapp_bot_sessions`

Colunas principais:

- `id` (uuid, PK)
- `tenant_id` (uuid)
- `channel` (default `whatsapp`)
- `provider`
- `contact_phone`
- `contact_identifier`
- `status`
- `current_flow`
- `current_step`
- `state` (json)
- `meta` (json)
- `last_payload` (json)
- `last_inbound_message_type`
- `last_inbound_message_at`
- `last_outbound_message_at`
- `created_at`, `updated_at`

Índices e chave operacional:

- `unique (tenant_id, channel, contact_phone)`
- índices por `tenant_id/channel`, `tenant_id/provider`, `tenant_id/last_inbound_message_at`.

## Observações de estado

- `state` e `meta` são serializados em JSON para evolução do fluxo.
- O isolamento por tenant é garantido por `tenant_id` + chave única de telefone.
- Não existe coluna física `last_interaction_at`; na prática, a interação é inferida por:
  - `last_inbound_message_at`
  - `last_outbound_message_at`.