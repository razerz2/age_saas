# Overview

Separacao de dominio:

- `WhatsApp Oficial > Templates Oficiais Platform`
  - catalogo global oficial Meta (`whatsapp_official_templates`)
- `WhatsApp Oficial > Templates Oficiais Tenant`
  - baseline padrao oficial tenant (eventos clinicos) no mesmo catalogo global (`whatsapp_official_templates`)
- `WhatsApp Nao Oficial`
  - modulo separado (sem relacao com o oficial tenant)

O modulo `Templates Oficiais Tenant`:

- e uma visao/CRUD separada para as keys oficiais do dominio Tenant (clinico):
  - `appointment.pending_confirmation`
  - `appointment.confirmed`
  - `appointment.canceled`
  - `appointment.expired`
  - `waitlist.joined`
  - `waitlist.offered`
- nao lista eventos SaaS (`invoice.*`, `subscription.*`, `tenant.*`, `security.*`);
- usa o mesmo catalogo global da Meta (nao duplica linhas em outra tabela);
- nao se confunde com o baseline nao oficial (`tenant-default-notification-templates`);
- permanece no dominio oficial/Meta.

Baseline tenant oficial (padrao atual):

- provider: `whatsapp_business`
- category: `UTILITY`
- language: `pt_BR`
- keys:
  - `appointment.pending_confirmation`
  - `appointment.confirmed`
  - `appointment.canceled`
  - `appointment.expired`
  - `waitlist.joined`
  - `waitlist.offered`

Estrutura tecnica dos templates tenant oficiais:

- `key`
- `meta_template_name`
- `provider`
- `category`
- `language`
- `body_text`
- `variables`
- `sample_variables`
- `status`
- `version`
- `meta_template_id`
- `meta_response`
- `last_synced_at`

Regra operacional importante:

- somente status `approved` e apto para uso real no envio oficial Meta.

Fluxos implementados no modulo:

1. Enviar/Submeter para Meta
2. Sincronizar status na Meta (atualiza `meta_response`, `status`, `meta_template_id`, `last_synced_at`)
3. Teste manual (somente quando apto)
   - usa lookup por nome canonico remoto quando houver divergencia (`tenant_...` vs remoto)
   - usa o schema remoto aprovado (placeholders efetivos do BODY)
   - alinha os valores semanticamente por `key/slot` quando o local diverge do remoto
