# Backend

## Arquitetura em camadas

### 1) Provider / Canal

Responsabilidade:

- receber payload cru do provider;
- normalizar inbound em DTO único;
- enviar outbound de forma padronizada.

Componentes principais:

- `App\Services\Tenant\WhatsAppBot\Provider\Contracts\WhatsAppBotProviderAdapterInterface`
- `...\Provider\WhatsAppBotProviderAdapterFactory`
- `...\Provider\WhatsAppBusinessBotProviderAdapter`
- `...\Provider\ZApiBotProviderAdapter`
- `...\Provider\WahaBotProviderAdapter`
- `...\Provider\AbstractWhatsAppBotProviderAdapter`
- `...\Provider\WhatsAppBotRuntimeConfigApplier`

### 2) Conversation / Orquestração

Responsabilidade:

- controlar sessão e estado;
- rotear intenção;
- conduzir fluxo guiado por step;
- tratar fallback e recuperação.

Componentes principais:

- `App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotConversationOrchestrator`
- `App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotIntentRouter`
- `App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotSessionService`

### 3) Domínio

Responsabilidade:

- executar regras reais de agenda e paciente;
- sem depender de payload cru de provider.

Componentes principais:

- `App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotAppointmentService`
- `App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotPatientService`
- `App\Services\Tenant\WhatsAppBot\Domain\WhatsAppBotDomainService`

## Ponto único de entrada inbound

- `App\Services\Tenant\WhatsAppBot\WhatsAppBotInboundMessageProcessor`

Fluxo técnico:

1. valida tenant atual;
2. valida feature `whatsapp_bot` no plano;
3. resolve provider efetivo via `WhatsAppBotProviderResolver`;
4. aplica configuração runtime do provider;
5. normaliza inbound com adapter;
6. abre/cria sessão;
7. executa orquestrador;
8. envia outbound pelo mesmo adapter;
9. registra resultado consolidado.

## Resolução do provider efetivo do bot

- Serviço: `App\Services\Tenant\WhatsAppBotConfigService`
- Resolver: `App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderResolver`

Regras:

- `provider_mode=shared_with_notifications` usa configuração efetiva de notificações;
- `provider_mode=dedicated` usa `whatsapp_bot.*`;
- provider suportado: `whatsapp_business`, `zapi`, `waha`;
- configuração inválida gera `WhatsAppBotConfigurationException`.

## DTOs normalizados

- `InboundMessage`
- `OutboundMessage`
- `ConversationResult`
- `InboundProcessingResult`

## Sessão conversacional

- aberta por `tenant_id + channel + contact_phone` (telefone normalizado);
- estado principal:
  - `current_flow`
  - `current_step`
  - `state` (JSON)
  - `meta` (JSON)
- reset seguro quando sessão é detectada como corrompida.

## Fluxo conversacional atual (MVP)

Implementado no `WhatsAppBotConversationOrchestrator`:

- Menu principal (`1`, `2`, `3` + textos equivalentes).
- Fluxo de agendamento:
  - especialidade (se aplicável)
  - profissional
  - data
  - horário
  - confirmação.
- Fluxo de visualização de próximos agendamentos.
- Fluxo de cancelamento com confirmação.
- Comandos globais: `menu`, `inicio`, `0` (e variações definidas no router).

## Integração com domínio real de agenda

`WhatsAppBotAppointmentService` reutiliza serviços reais:

- disponibilidade: `DoctorSlotFinder`;
- criação: validação de `StoreAppointmentRequest` + criação em `Appointment`;
- cancelamento: atualização real + `NotificationDispatcher` + `WaitlistService::onSlotReleased`.

Proteções aplicadas:

- impede agendamento duplicado ativo no mesmo slot;
- valida se slot ainda está disponível no momento da confirmação;
- valida regra de cancelamento (`appointments.allow_cancellation` e antecedência mínima).

## Observabilidade e logs

Eventos técnicos principais:

- `whatsapp_bot.inbound.received`
- `whatsapp_bot.inbound.processed`
- `whatsapp_bot.inbound.ignored`
- `whatsapp_bot.inbound.failed`
- `whatsapp_bot.flow.transition`
- `whatsapp_bot.flow.step`
- `whatsapp_bot.intent.routed`

Campos comuns de rastreio:

- `tenant_id`, `provider`, `phone`, `flow`, `step`, `action`, `result`, `processing_ms`.