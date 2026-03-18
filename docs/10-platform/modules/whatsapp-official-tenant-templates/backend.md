# Backend

Componentes principais:

- Controller:
  - `App\Http\Controllers\Platform\WhatsAppOfficialTenantTemplateController`
- Model (catalogo global):
  - `App\Models\Platform\WhatsAppOfficialTemplate` (tabela `whatsapp_official_templates`)

Servicos reaproveitados do modulo `whatsapp-official-templates`:

- `App\Services\Platform\WhatsAppOfficialTemplateService` (submissao + sync com Meta)
- `App\Services\Platform\WhatsAppOfficialMessageService` (envio de teste manual)

Regras aplicadas na validacao:

- `provider` deve ser `whatsapp_business`;
- `key` deve estar no baseline oficial tenant (keys clinicas suportadas);
- no teste manual:
  - exige template remoto existente e `APPROVED` para o nome/idioma consultado;
  - valida numero de destino;
  - valida variaveis obrigatorias.

Fluxo de sincronizacao (Meta):

- faz sync do template do catalogo global com a Meta e persiste:
  - `meta_template_id`, `meta_response`, `status`, `last_synced_at`

Fluxo de teste manual (Meta):

1. localizar template oficial tenant (registro em `whatsapp_official_templates` por `key`)
2. sincronizar e validar status remoto
3. localizar snapshot remoto (nome/idioma), incluindo fallback de nome canonico quando necessario
4. extrair schema remoto aprovado (placeholders efetivos do `BODY`)
5. alinhar variaveis de envio semanticamente por `key/slot` (evita alinhamento ingenuo por posicao quando local diverge)
6. enviar mensagem via Meta Cloud API
