# Reports - Routes

Fonte: `routes/tenant/reports.php`.

Todas as rotas abaixo estao sob:

- Prefixo: `/workspace/{slug}/reports`
- Middleware: `module.access:reports`
- Prefixo de nome: `tenant.reports.*`

## Rotas por relatorio

### Agendamentos

- `GET /appointments` -> `reports.appointments`
- `GET /appointments/grid-data` -> `reports.appointments.grid-data`
- `GET /appointments/export.xlsx` -> `reports.appointments.export.xlsx`
- `GET /appointments/export.pdf` -> `reports.appointments.export.pdf`

### Pacientes

- `GET /patients` -> `reports.patients`
- `GET /patients/grid-data` -> `reports.patients.grid-data`
- `GET /patients/export.xlsx` -> `reports.patients.export.xlsx`
- `GET /patients/export.pdf` -> `reports.patients.export.pdf`

### Medicos

- `GET /doctors` -> `reports.doctors`
- `GET /doctors/grid-data` -> `reports.doctors.grid-data`
- `GET /doctors/export.xlsx` -> `reports.doctors.export.xlsx`
- `GET /doctors/export.pdf` -> `reports.doctors.export.pdf`

### Recorrencias

- `GET /recurring` -> `reports.recurring`
- `GET /recurring/grid-data` -> `reports.recurring.grid-data`
- `GET /recurring/export.xlsx` -> `reports.recurring.export.xlsx`
- `GET /recurring/export.pdf` -> `reports.recurring.export.pdf`

### Formularios

- `GET /forms` -> `reports.forms`
- `GET /forms/grid-data` -> `reports.forms.grid-data`
- `GET /forms/export.xlsx` -> `reports.forms.export.xlsx`
- `GET /forms/export.pdf` -> `reports.forms.export.pdf`

### Portal do Paciente

- `GET /portal` -> `reports.portal`
- `GET /portal/grid-data` -> `reports.portal.grid-data`
- `GET /portal/export.xlsx` -> `reports.portal.export.xlsx`
- `GET /portal/export.pdf` -> `reports.portal.export.pdf`

### Notificacoes

- `GET /notifications` -> `reports.notifications`
- `GET /notifications/grid-data` -> `reports.notifications.grid-data`
- `GET /notifications/export.xlsx` -> `reports.notifications.export.xlsx`
- `GET /notifications/export.pdf` -> `reports.notifications.export.pdf`

## Parametros aceitos em `grid-data`

Padrao comum:

- `page` (1-based)
- `per_page` (`10|25|50|100`)
- `search` (string)
- `sort` (id da coluna)
- `dir` (`asc|desc`)

Filtros adicionais dependem de cada relatorio (ex.: `date_from`, `date_to`, `doctor_id`, etc.).
