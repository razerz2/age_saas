# Campanhas — Frontend

Fontes:

- `resources/js/tenant/pages/campaigns.js`
- Views: `resources/views/tenant/campaigns/*`

## Páginas JS

- Entrada principal do módulo:
  - `resources/js/tenant/pages/campaigns.js`
  - Função exportada: `init()`
    - `initCampaignsGrid()`
    - `initCampaignRunsGrid()`
    - `initCampaignRecipientsGrid()`
    - `initCampaignForm()`

## Grid.js (server-side)

### Index (Campaigns)

DOM esperado:

- Wrapper: `#campaigns-grid-wrapper` com:
  - `data-grid-url` (ex.: `tenant.campaigns.grid`)
  - `data-show-url-template` (usado para clique de linha)
  - `data-row-click-link-selector` (default `a[title="Ver"]`)
- Target: `#campaigns-grid`

Parâmetros enviados ao endpoint `grid-data`:

- Paginação:
  - `page` (1-based)
  - `limit` (10/25/50/100)
- Busca:
  - `search` (string)
- Ordenação:
  - `sort[column]` = id da coluna
  - `sort[direction]` = `asc|desc`

Persistência de page size:

- `localStorage` key: `tenant_campaigns_page_size`

### Runs / Recipients

- Runs:
  - Wrapper: `#campaign-runs-grid-wrapper` (`data-grid-url`)
  - `localStorage` key: `tenant_campaign_runs_page_size`
- Recipients:
  - Wrapper: `#campaign-recipients-grid-wrapper` (`data-grid-url`)
  - `localStorage` key: `tenant_campaign_recipients_page_size`

## Form (create/edit)

View:

- `resources/views/tenant/campaigns/partials/form.blade.php` usa `id="campaign-form"`.
- O form expõe `data-asset-upload-url` para o endpoint de upload:
  - `tenant.campaigns.assets.store` (`POST /campaigns/assets`)

### Seleção de canais

- Se só existe um canal disponível:
  - O form injeta `input[type=hidden] name="channels[]" data-fixed-channel="true"`.
- Se existem dois canais:
  - Checkboxes `.js-channel-checkbox` controlam seções:
    - `#campaign-email-section`
    - `#campaign-whatsapp-section`
- Ao alternar canais, o JS:
  - Habilita/desabilita inputs das seções.
  - Atualiza audiência “require” (campos hidden):
    - `#audience-require-email` (`audience_json[require][email]`)
    - `#audience-require-whatsapp` (`audience_json[require][whatsapp]`)

### Tipo: manual x automated

- Select `#campaign-type`:
  - Mostra/oculta automação em `#campaign-automation-section`.
  - Inputs `.js-automation-input` são habilitados apenas quando `type=automated`.

### Regras (campanha agendada)

- Bloco no form: `#campaign-rules-section` (visivel apenas para `type=automated`).
- Lista de condicoes: `#campaign-rules-list`.
- Adicao de condicao: `#campaign-rules-add`.
- Cada linha possui:
  - Campo (`.js-campaign-rule-field`)
  - Operador (`.js-campaign-rule-operator`)
  - Valor (`.js-campaign-rule-value-input` / `.js-campaign-rule-value-select`)
- O JS oculta o valor para operadores sem valor (`is_null`, `is_not_null`, `birthday_today`) e reindexa os nomes `rules_json[conditions][i][...]` ao adicionar/remover linhas.

### WhatsApp: text vs media (URL vs upload)

Elementos usados no form:

- `#whatsapp-message-type` controla wrappers:
  - `#whatsapp-text-wrapper`
  - `#whatsapp-media-wrapper`
- `#whatsapp-media-source` controla:
  - `#whatsapp-media-url-wrapper`
  - `#whatsapp-media-asset-wrapper`

Upload de mídia (source `upload`):

- Input file: `#whatsapp-media-upload-file`
- Botão: `#whatsapp-media-upload-btn`
- Feedback: `#whatsapp-media-upload-feedback`
- Asset id (hidden/input): `#whatsapp-media-asset-id`

O JS:

- Ajusta `accept` do input conforme `media.kind`.
- Faz `fetch(POST)` para `data-asset-upload-url` com `FormData(file, kind)` e header `X-CSRF-TOKEN`.
- Mapeia kind por tipo:
  - `image -> whatsapp_image`, `video -> whatsapp_video`, `document -> whatsapp_document`, `audio -> whatsapp_audio`.
- Na submissão do form:
  - Bloqueia (`preventDefault`) se `message_type=media`, `source=upload` e `asset_id` estiver vazio.

### Upload de anexos de email

Elementos:

- Input file multiple: `#email-attachments-upload-input`
- Botão: `#email-attachments-upload-btn`
- Lista: `#email-attachments-list` (com `data-max-items="3"` e `data-next-index`)
- Feedback: `#email-attachments-upload-feedback`

O JS:

- Envia anexos via `uploadAsset(file, 'email_attachment')`.
- Limita a 3 anexos (slots disponíveis calculados pelo DOM).
- Anexos viram inputs hidden:
  - `content_json[email][attachments][i][source] = upload`
  - `...asset_id`, `...filename`, `...mime`, `...size`

## Observações de UX (MVP)

- Index mostra alerta e desabilita “Nova Campanha” quando `moduleEnabled=false`.
- Show desabilita botões de envio quando:
  - Módulo não está habilitado no tenant, ou
  - A campanha contém canais não disponíveis no tenant.
- Show de campanha `automated`:
  - Não exibe ações de `Iniciar agora` e `Agendar envio` como caminho principal.
  - Exibe card informando disparo automático via programação.
  - Mantém `Pausar/Retomar` disponível.
