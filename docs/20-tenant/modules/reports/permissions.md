# Reports - Permissions

## Middleware de modulo

Rotas de relatorios usam:

- `module.access:reports`

Escopo: apenas modulo Tenant `reports`.

## Filtro por medico

Controllers com restricao por medico (via trait `HasDoctorFilter`):

- `AppointmentReportController`
- `DoctorReportController`
- `RecurringReportController`

Comportamento esperado:

- `admin`: visualiza todos
- `doctor`: visualiza apenas proprio escopo
- `user`: visualiza medicos permitidos (quando configurado)

## Acoes de linha (links)

Partials `actions` usam rotas de modulos relacionados (`appointments`, `patients`, `doctors`, etc.).
Se o usuario nao tiver acesso a esses modulos, o bloqueio ocorre nas respectivas rotas de destino.
