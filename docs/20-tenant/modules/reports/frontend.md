# Reports - Frontend

Fonte: `resources/js/tenant/pages/reports.js`.

## Inicializacao

A pagina usa `data-page="reports"`, carregando `reports.js` via `resources/js/tenant/app.js`.

`reports.js` identifica a pagina pelo bloco:

- `[id^="reports-"][data-report-type][data-grid-url]`

## Grid.js server-side

Para cada tipo de relatorio, o JS define:

- colunas
- colunas HTML (`status_badge`, `actions`, etc.) com `gridjs.html`
- callbacks de payload (`summary`/`chart` quando existentes)

## Estado da tela

Estado mantido no frontend:

- `filters` (form de filtros)
- `search`
- `sort`
- `dir`
- `page`
- `perPage`

Esse estado:

- monta URL do `grid-data`
- sincroniza querystring da pagina (`history.replaceState`)
- alimenta URLs de exportacao

## Exportar Excel/PDF

Botao com `data-export-format="excel|pdf"`:

- Usa URL base do config (`data-export-excel-url` / `data-export-pdf-url`)
- Anexa os mesmos parametros ativos (`filters`, `search`, `sort`, `dir`)

## Agendamentos: resumo e graficos

No tipo `appointments`, o payload `gridData` tambem retorna:

- `summary`
- `chart.evolution`
- `chart.mode`
- `chart.byDoctor`
- `chart.heatmap`

O JS atualiza cards e graficos (`Chart.js`) no mesmo ciclo de carga da grid.

## DataTables removido do modulo

- Nao ha mais inicializacao DataTables em `reports.js`.
- As views de relatorio nao usam mais `<table id="reports-table">`.
