# Overview

Modulo da Platform para:

- cadastrar templates internos do provider oficial (`whatsapp_business`);
- listar e visualizar versoes;
- enviar template para Meta;
- sincronizar status com Meta;
- testar envio manual do template oficial na tela de detalhe (Show);
- arquivar e versionar sem editar template aprovado.

Escopo de dominio:

- este modulo nao deve conter templates operacionais de clinica (`appointment.*`, `waitlist.*`);
- notificacoes operacionais continuam no Tenant (catalogo `config/notification_templates.php` + overrides na tabela tenant `notification_templates`);
- o baseline operacional global fica no modulo Platform `tenant-default-notification-templates` (tabela `tenant_default_notification_templates`), sem misturar com templates oficiais Meta.

Baseline SaaS (seeder Platform):

- `invoice.created`
- `invoice.upcoming_due`
- `invoice.overdue`
- `tenant.suspended_due_to_overdue`
- `security.2fa_code`
- `tenant.welcome`
- `subscription.created`
- `subscription.recovery_started`
- `credentials.resent`

Integracao runtime Platform:

- eventos SaaS da Platform (fatura, assinatura, recovery, boas-vindas, reenvio de credenciais e 2FA) consultam o catalogo oficial por `key`;
- somente versoes `approved` sao consideradas aptas para envio oficial;
- quando template estiver ausente/inapto, o fluxo registra log explicito e nao envia mensagem hardcoded.

## Teste manual de template

Disponivel na tela Show do template oficial (acao `Testar template`).

Objetivo:

- permitir validar rapidamente o envio oficial de um template especifico para um numero de destino, sem depender do disparo por evento SaaS.

Regras:

- o teste manual e bloqueado para templates com status diferente de `approved` (`draft`, `pending`, `rejected`, `archived`);
- o envio de teste respeita o schema remoto aprovado salvo em `meta_response` quando aplicavel (ex.: `AUTHENTICATION` com placeholders e/ou botoes dinamicos).

Restricao: uso exclusivo para provider oficial. Se `WHATSAPP_PROVIDER` for diferente de `whatsapp_business`, o modulo e bloqueado.

API da Meta usada: Graph API `v22.0`.

Regras da Meta atendidas no modulo:

- placeholders do body (`{{1}}`, `{{2}}`, ...) nao podem estar no inicio/fim do texto;
- ao criar/submeter template com placeholders, a Meta exige exemplos de texto para cada variavel (persistidos em `sample_variables`).
