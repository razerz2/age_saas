# Reports - Overview

## Objetivo

Padronizar o modulo Tenant de relatorios para o padrao do projeto:

- Grid.js server-side em todas as listagens.
- Endpoints `grid-data` por relatorio.
- Exportacoes `export.xlsx` e `export.pdf` com os mesmos filtros/search/sort ativos na tela.

## Escopo aplicado

Relatorios do Tenant migrados do fluxo DataTables para Grid.js:

- Agendamentos
- Pacientes
- Medicos
- Recorrencias
- Formularios
- Portal do Paciente
- Notificacoes

Nao houve alteracao em modulos Platform.

## Resultado tecnico

- Rotas antigas `POST .../data` foram substituidas por `GET .../grid-data`.
- Cada controller usa query-base compartilhada entre:
  - `gridData()`
  - `exportExcel()`
  - `exportPdf()`
- Colunas HTML seguem partials (`status_badge`, `actions`, etc.).
- Exportacao Excel usa `maatwebsite/excel` com `FromQuery` + chunk.
- Exportacao PDF usa `barryvdh/laravel-dompdf` com limite seguro de linhas.
