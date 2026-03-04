# Reports - Backend

Fontes:

- `app/Http/Controllers/Tenant/Reports/*ReportController.php`
- `app/Http/Controllers/Tenant/Reports/Concerns/HandlesReportRequests.php`
- `app/Exports/Tenant/Reports/ReportQueryExport.php`

## Controllers

- `AppointmentReportController`
- `PatientReportController`
- `DoctorReportController`
- `RecurringReportController`
- `FormReportController`
- `PortalReportController`
- `NotificationReportController`

Todos implementam:

- `index()`
- `gridData(Request $request)`
- `exportExcel(Request $request)`
- `exportPdf(Request $request)`

## Contrato `gridData`

Resposta JSON:

- `data` (linhas para Grid.js)
- `meta`
  - `page`, `per_page`, `total`, `last_page`, `from`, `to`
- `summary` (quando aplicavel)
- `chart` (no relatorio de agendamentos)

## Query-base reutilizada

Cada controller possui uma query-base privada reaproveitada em:

- gridData
- exportExcel
- exportPdf

Isso evita divergencia entre resultado de tela e exportacao.

## Exportacao Excel

- Pacote: `maatwebsite/excel`
- Classe: `App\Exports\Tenant\Reports\ReportQueryExport`
- Estrategia:
  - `FromQuery`
  - `WithMapping`
  - `WithHeadings`
  - `WithChunkReading` (chunk padrao: 1000)

Comportamento: exporta todas as linhas que satisfazem filtros/search/sort ativos.

## Exportacao PDF

- Pacote: `barryvdh/laravel-dompdf`
- Render via Blade por relatorio (`resources/views/tenant/reports/*/pdf.blade.php`)
- Limite de seguranca:
  - `PDF_MAX_ROWS = 5000` por controller
  - Se exceder, aplica truncamento com aviso no PDF

## Dependencias adicionadas

- `maatwebsite/excel:^3.1.56`
- `barryvdh/laravel-dompdf:^3.1`
