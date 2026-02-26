# Campanhas — Views

Fontes: `resources/views/tenant/campaigns/*` e `resources/views/layouts/tailadmin/*`.

## Páginas (Blade)

- Listagem:
  - `resources/views/tenant/campaigns/index.blade.php`
- Criar:
  - `resources/views/tenant/campaigns/create.blade.php`
  - `resources/views/tenant/campaigns/partials/form.blade.php` (form compartilhado)
- Editar:
  - `resources/views/tenant/campaigns/edit.blade.php`
  - `resources/views/tenant/campaigns/partials/form.blade.php`
- Visualizar:
  - `resources/views/tenant/campaigns/show.blade.php`

## Partials (campanhas)

- Ações e badges:
  - `resources/views/tenant/campaigns/partials/actions.blade.php` (ações no Grid.js)
  - `resources/views/tenant/campaigns/partials/status_badge.blade.php` (draft/active/paused/archived/blocked)
  - `resources/views/tenant/campaigns/partials/channel_badges.blade.php` (canais e alerta de indisponível)
- Resumos:
  - `resources/views/tenant/campaigns/partials/content_email.blade.php`
  - `resources/views/tenant/campaigns/partials/content_whatsapp.blade.php`
  - `resources/views/tenant/campaigns/partials/audience_summary.blade.php`
  - `resources/views/tenant/campaigns/partials/automation_summary.blade.php`
- Form:
  - `resources/views/tenant/campaigns/partials/form.blade.php`

## Histórico (Runs e Recipients)

- Runs:
  - `resources/views/tenant/campaigns/runs/index.blade.php`
  - `resources/views/tenant/campaigns/runs/partials/actions.blade.php`
  - `resources/views/tenant/campaigns/runs/partials/status_badge.blade.php`
- Recipients:
  - `resources/views/tenant/campaigns/recipients/index.blade.php`
  - `resources/views/tenant/campaigns/recipients/partials/actions.blade.php`
  - `resources/views/tenant/campaigns/recipients/partials/status_badge.blade.php`

## Padrões de UI usados no módulo

- Layout base: `@extends('layouts.tailadmin.app')`
- Breadcrumbs:
  - `index`, `runs/index`, `recipients/index` e `show` seguem o padrão Dashboard → Campanhas → (Campanha) → subpágina.
- Tailwind + TailAdmin:
  - Cards com `rounded-xl`, `shadow-sm`, bordas e suporte a `dark:*`.
- Ícones:
  - Buttons e badges usam Material Design Icons (`mdi`) e o componente `x-icon` (TailAdmin).

## Comportamento quando o módulo não está habilitado (sem canais)

### Index

Em `resources/views/tenant/campaigns/index.blade.php`:

- Quando `moduleEnabled=false`:
  - Exibe alerta “Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.”
  - CTA “Configurar Integrações” (link para `tenant.integrations.index` quando existe).
  - Botão “Nova Campanha” fica desabilitado.
- A listagem (Grid.js) continua renderizando, mas:
  - As ações do grid são renderizadas com `moduleEnabled=false`, desabilitando Editar/Excluir (`partials/actions.blade.php`).

### Form (create/edit)

- As rotas de create/store/edit/update/destroy são protegidas por middleware `campaign.module.enabled` (ver `routes.md` e `permissions.md`).
- O form controla seções por canal:
  - `#campaign-email-section` e `#campaign-whatsapp-section` são exibidas/ocultadas e seus inputs habilitados/desabilitados via JS.

### Show

Em `resources/views/tenant/campaigns/show.blade.php`:

- Se `moduleEnabled=false`, mostra alerta e desabilita ações de envio.
- Se a campanha tem canais que não estão disponíveis no tenant:
  - Mostra alerta com a lista de canais indisponíveis.
  - Desabilita ações de envio (`$dispatchActionsEnabled = $moduleEnabled && !$hasUnavailableChannels`).

