# Reports - Views

Fonte: `resources/views/tenant/reports/**`.

## Paginas principais

- `tenant/reports/index.blade.php` (hub de relatorios)
- `tenant/reports/appointments/index.blade.php`
- `tenant/reports/patients/index.blade.php`
- `tenant/reports/doctors/index.blade.php`
- `tenant/reports/recurring/index.blade.php`
- `tenant/reports/forms/index.blade.php`
- `tenant/reports/portal/index.blade.php`
- `tenant/reports/notifications/index.blade.php`

## Config de frontend por pagina

Cada pagina de relatorio define um bloco de configuracao no topo com:

- `data-report-type`
- `data-grid-url`
- `data-export-excel-url`
- `data-export-pdf-url`

Consumido por `resources/js/tenant/pages/reports.js`.

## Partials de colunas HTML

Padrao de colunas renderizadas no backend:

- `appointments/partials/*` (`mode_badge`, `status_badge`, `actions`)
- `patients/partials/actions`
- `doctors/partials/*`
- `forms/partials/*`
- `portal/partials/*`
- `notifications/partials/*`
- `recurring/partials/*`

## Templates PDF

Blade dedicado por relatorio:

- `appointments/pdf.blade.php`
- `patients/pdf.blade.php`
- `doctors/pdf.blade.php`
- `forms/pdf.blade.php`
- `portal/pdf.blade.php`
- `notifications/pdf.blade.php`
- `recurring/pdf.blade.php`

Layout: tabela simples, legivel, com cabecalho de geracao e filtros ativos.
