# Overview

## O que é

O `WhatsApp Bot` é o módulo de atendimento automatizado por WhatsApp no contexto do tenant. Ele recebe mensagens inbound de múltiplos providers, normaliza a entrada e executa um fluxo conversacional único, independente do canal técnico.

## Para que serve

No estado atual (MVP), o módulo oferece autoatendimento guiado por menu para pacientes identificados por telefone:

- agendar consulta;
- visualizar agendamentos futuros;
- cancelar agendamento.

## Escopo do MVP

Inclui:

- menu inicial e fallback;
- identificação de paciente por telefone (com criação simples quando necessário);
- sessão conversacional com `current_flow`, `current_step` e `state`;
- integração com regras reais de agenda para disponibilidade, criação e cancelamento;
- logs técnicos estruturados.

Não inclui:

- NLP/IA livre;
- engine de conversa aberta;
- webhook outbound separado;
- fluxos fora de agenda.

## Funcionamento em alto nível

1. Provider envia payload para webhook do tenant.
2. `WhatsAppBotInboundMessageProcessor` valida feature/tenant e resolve provider efetivo do bot.
3. Adapter do provider normaliza payload para `InboundMessage`.
4. `WhatsAppBotSessionService` abre/cria sessão por telefone normalizado.
5. `WhatsAppBotConversationOrchestrator` decide ação conforme fluxo/step e input.
6. Serviços de domínio executam operação real (agenda/paciente).
7. Adapter envia respostas `OutboundMessage`.
8. Processamento e transições são registrados em log.

## Bot x Notificações

- Notificações (`notifications`) enviam mensagens transacionais orientadas a eventos.
- Bot (`whatsapp_bot`) é conversacional e orientado a input do usuário.
- O provider do bot é independente do provider de notificações:
  - pode herdar via `shared_with_notifications`;
  - ou usar `dedicated` com configuração própria.

## Regras de ativação

O bot só processa conversa quando:

- a feature de plano `whatsapp_bot` está disponível;
- `whatsapp_bot.enabled = true`;
- a configuração do provider efetivo está válida.

Quando `enabled = false`, o inbound é ignorado ou recebe mensagem de indisponibilidade configurada (`whatsapp_bot.disabled_message`).