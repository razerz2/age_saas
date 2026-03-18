# Overview

Modulo da Platform para:

- cadastrar templates internos do provider oficial (`whatsapp_business`);
- listar e visualizar versoes;
- enviar template para Meta;
- sincronizar status com Meta;
- testar envio manual do template oficial na tela de detalhe (Show);
- arquivar e versionar sem editar template aprovado.

Escopo de dominio:

- este modulo e o catalogo global oficial da Meta (`whatsapp_official_templates`);
- inclui templates oficiais de dominio Platform (SaaS) e templates oficiais de dominio Tenant (clinico);
- notificacoes nao oficiais continuam separadas no modulo `tenant-default-notification-templates`.

Navegacao Platform:

- `WhatsApp Oficial`:
  - `Templates Oficiais Platform`
  - `Templates Oficiais Tenant`
- `WhatsApp Nao Oficial`:
  - `Templates Internos Platform`
  - `Templates Padrao Tenant`

Observacao de arquitetura:

- `Templates Oficiais Platform` e `Templates Oficiais Tenant` sao navegacoes/CRUDs separados sobre o mesmo catalogo global (`whatsapp_official_templates`);
- a separacao e por dominio (keys SaaS vs keys clinicas) e por guard-rails de UX, nao por duplicacao de dados.

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

Baseline Tenant oficial (seeder Platform):

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

Padrao de variaveis tenant oficial:

- `patient_name`, `clinic_name`, `appointment_date`, `appointment_time`, `professional_name`
- `appointment_confirm_link`, `appointment_cancel_link`, `appointment_details_link`
- `waitlist_offer_expires_at`, `waitlist_offer_link`

Observacao:

- os templates tenant oficiais entram no catalogo global;
- o modulo `whatsapp-official-tenant-templates` e uma visao/CRUD separada do dominio Tenant (clinico) dentro do mesmo catalogo global;
- neste momento, nao ha configuracao por tenant (mapeamento tenant -> template) documentada como fonte de verdade: a separacao e por dominio/keys.

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
