# Permissions

Integracao com padrao Platform:

- modulo base: `whatsapp_official_templates` (via `module.access`).

Permissoes de acao (policy):

- `whatsapp_official_templates.view`
- `whatsapp_official_templates.create`
- `whatsapp_official_templates.edit_draft`
- `whatsapp_official_templates.send_to_meta`
- `whatsapp_official_templates.sync_status`
- `whatsapp_official_templates.archive`

Comportamento:

- o acesso ao modulo depende da chave base `whatsapp_official_templates` (middleware `module.access`);
- as chaves de acao estao mapeadas em policy para controle por rota, sem alterar o padrao atual da Platform.
- o teste manual (`test-send`) usa a mesma permissao da acao de envio para Meta (`send_to_meta`).
