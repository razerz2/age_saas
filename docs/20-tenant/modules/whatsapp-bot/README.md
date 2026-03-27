# Módulo: WhatsApp Bot (Tenant)

Documentação do módulo de Bot de WhatsApp no tenant, com foco em configuração por plano, resolução de provider, arquitetura de processamento inbound e fluxo conversacional guiado.

## Objetivo

Permitir atendimento automatizado por WhatsApp, sem acoplamento ao provider específico, usando fluxo guiado para:

- agendar consulta;
- visualizar próximos agendamentos;
- cancelar agendamento.

## Funcionalidades principais

- Aba própria em `Tenant > Configurações` (`bot-whatsapp`).
- Feature flag comercial por plano: `whatsapp_bot`.
- Resolução de provider efetivo em dois modos:
  - `shared_with_notifications`;
  - `dedicated`.
- Suporte a providers:
  - `whatsapp_business` (oficial);
  - `zapi`;
  - `waha`.
- Sessão conversacional por telefone normalizado.
- Logs técnicos estruturados para rastreabilidade.
- Fallback e recuperação de fluxo (`menu`, `inicio`, `0`).

## Dependências

- Plano do tenant com a feature `whatsapp_bot` habilitada.
- Configuração válida do provider efetivo do bot.
- Serviços de domínio de agenda já existentes (disponibilidade, criação e cancelamento).

## Arquivos deste módulo

- `overview.md` — visão geral funcional e escopo.
- `routes.md` — rotas, middlewares e observações de entrada.
- `backend.md` — arquitetura e serviços principais.
- `frontend.md` — aba de configuração e comportamento da UI.
- `database.md` — settings e sessão conversacional.
- `permissions.md` — feature flag, plano e regras de acesso.
- `troubleshooting.md` — problemas comuns e diagnóstico.

## Fontes consultadas (paths)

- `routes/tenant.php`
- `app/Http/Controllers/Tenant/WhatsAppBotWebhookController.php`
- `app/Http/Controllers/Tenant/SettingsController.php`
- `app/Services/Tenant/WhatsAppBotConfigService.php`
- `app/Services/Tenant/WhatsAppBot/`
- `app/Models/Tenant/WhatsAppBotSession.php`
- `database/migrations/tenant/2026_03_27_000300_create_whatsapp_bot_sessions_table.php`
- `resources/views/tenant/settings/tabs/bot-whatsapp.blade.php`
- `resources/views/tenant/settings/index.blade.php`