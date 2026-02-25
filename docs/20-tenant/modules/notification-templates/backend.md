# Backend

## 1) Catálogo default (imutável)

Arquivo:

- `config/notification_templates.php`

Formato:

- `channels`: lista de canais suportados (ex.: `email`, `whatsapp`)
- `templates`: map por `key`, contendo:
  - `label`
  - bloco por canal:
    - `email`: `subject` (opcional) + `content` (obrigatório)
    - `whatsapp`: `content` (obrigatório)

Chaves ativas no fluxo (ver módulo appointments):

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

## 2) Overrides por tenant

Tabela/model:

- `app/Models/Tenant/NotificationTemplate.php` (connection `tenant`, table `notification_templates`)

Service:

- `app/Services/Tenant/NotificationTemplateService.php`

Métodos principais:

- `listKeys()` lista keys/labels/canais suportados a partir do catálogo.
- `getDefaultTemplate(channel, key)` retorna o default do config.
- `getOverride(tenantId, channel, key)` retorna override (ou null).
- `getEffectiveTemplate(tenantId, channel, key)` retorna default ou override (com `is_override`).
- `saveOverride(...)` faz upsert por `(tenant_id, channel, key)`.
- `restoreDefault(...)` remove override.

Validações:

- `channel` deve estar em `email|whatsapp`.
- `key` deve existir no catálogo.
- `content` é obrigatório.
- `subject` é obrigatório apenas quando `channel=email` e o default possui subject não vazio.

## 3) Renderer (placeholders `{{dot.notation}}`)

Arquivo:

- `app/Services/Tenant/TemplateRenderer.php`

Comportamento:

- Substitui `{{a.b.c}}` por `data_get($context, 'a.b.c')`.
- Se não existir no contexto, mantém o placeholder intacto.
- Não executa expressões/código.
- `extractPlaceholders($content)` retorna a lista de placeholders usados no texto.

## 4) Contexto padronizado (Appointment/Waitlist + links)

Arquivo:

- `app/Services/Tenant/NotificationContextBuilder.php`

Entrada:

- `buildForAppointment(Appointment $appointment)`
- `buildForWaitlistOffer(AppointmentWaitlistEntry $entry)`

Pontos importantes:

- Resolve `clinic.*` a partir do tenant atual (inclui `clinic.slug`).
- Resolve links públicos via nomes de rota (somente se a rota existir e o slug estiver disponível).
- Datas são formatadas para `d/m/Y`, `H:i` e `d/m/Y H:i` respeitando timezone do tenant.

## 5) Dispatcher (template efetivo + contexto + entrega)

Arquivo:

- `app/Services/Tenant/NotificationDispatcher.php`

Fluxo:

1. Carrega template efetivo (default/override) por canal/key.
2. Monta contexto (appointment/waitlist).
3. Renderiza subject/content.
4. Normaliza texto por canal (preserva `\n`).
5. Detecta placeholders desconhecidos e gera warning `unknown_placeholders`.
6. Entrega via sender real do canal.

API útil para debug:

- `buildMessageForAppointment($appointment, $key, ['email'|'whatsapp'])`

## 6) Senders reais (ponto único de entrega)

WhatsApp:

- `app/Services/Tenant/WhatsAppSender.php`
- Envia via `App\Services\WhatsappTenantService::send(...)`.

Email:

- `app/Services/Tenant/EmailSender.php`
- Envia via `App\Services\MailTenantService::send(...)`.

Ambos:

- Logam eventos estruturados (`whatsapp_real_send`, `email_real_send`).
- Registram auditoria persistente em `notification_deliveries` (success/error).
- Não devem montar texto manualmente: recebem o texto já renderizado do dispatcher.

