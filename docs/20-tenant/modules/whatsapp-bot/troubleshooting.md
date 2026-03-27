# Troubleshooting

## 1) Bot não responde

Checklist:

- Verifique se a feature `whatsapp_bot` está ativa no plano do tenant.
- Verifique se `whatsapp_bot.enabled=true`.
- Confirme se o provider efetivo está válido e com credenciais completas.
- Confirme que o provider do webhook (`{provider}`) coincide com o provider efetivo resolvido.

Pontos de log úteis:

- `whatsapp_bot.inbound.ignored`
- `whatsapp_bot.inbound.failed`
- `whatsapp_bot.inbound.received`

## 2) Webhook retorna erro/ignorado

Possíveis causas:

- `provider_mismatch`
- `payload_not_supported`
- `invalid_configuration`
- feature não disponível no plano

Verifique:

- rota correta: `/customer/{slug}/webhooks/whatsapp/bot/{provider}`;
- slug do tenant correto;
- payload esperado para o adapter correspondente.

## 3) Erro ao agendar

Causas comuns:

- horário não está mais disponível;
- tentativa de agendamento duplicado ativo;
- validação de request falhou no domínio real.

Referência:

- `WhatsAppBotAppointmentService::createAppointment(...)`
- `assertNoDuplicateAppointment(...)`
- `assertSlotStillAvailable(...)`

## 4) Erro ao cancelar

Causas comuns:

- cancelamento desabilitado em `appointments.allow_cancellation`;
- janela mínima de antecedência (`appointments.cancellation_hours`) não atendida;
- agendamento já cancelado.

Referência:

- `WhatsAppBotAppointmentService::cancelAppointment(...)`

## 5) Sessão travada / fluxo inconsistente

Recuperação para usuário final:

- enviar `menu`, `inicio` ou `0`.

Recuperação técnica:

- o serviço de sessão aplica reset seguro quando detecta estado corrompido (`whatsapp_bot.session.reset`).

## 6) Diferença de comportamento entre providers

Checklist:

- confirme normalização inbound do adapter usado;
- confirme se o provider runtime foi aplicado corretamente;
- compare logs com `provider`, `incoming_provider` e `provider_hint`.

Observação:

- outbound usa normalização textual comum (`normalizeOutboundText`) para reduzir diferenças de quebra de linha/formatação.

## 7) Problemas de encoding (mojibake)

Sintomas:

- acentuação quebrada em labels, mensagens ou textos de resposta.

Ações recomendadas:

- garantir UTF-8 sem BOM nos arquivos alterados;
- evitar conversões `utf8_encode/utf8_decode`;
- validar mensagens estáticas da UI e respostas do bot em ambiente real.
