# Online Appointments — Rotas

Fontes: `routes/tenant.php` (bloco ONLINE APPOINTMENTS).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

Prefixo dedicado e name prefix:

- Prefixo: `/workspace/{slug}/appointments/online`
- Name prefix: `tenant.online-appointments.`

### Rotas

- `GET /workspace/{slug}/appointments/online`
  - Name: `tenant.online-appointments.index`
  - Controller: `Tenant\\OnlineAppointmentController@index`

- `GET /workspace/{slug}/appointments/online/consultas-online/grid-data`
  - Name: `tenant.online-appointments.grid-data`
  - Controller: `Tenant\\OnlineAppointmentController@gridData`

- `GET /workspace/{slug}/appointments/online/{appointment}`
  - Name: `tenant.online-appointments.show`
  - Controller: `Tenant\\OnlineAppointmentController@show`
  - Restrição: `{appointment}` é UUID.

- `POST /workspace/{slug}/appointments/online/{appointment}/save`
  - Name: `tenant.online-appointments.save`
  - Controller: `Tenant\\OnlineAppointmentController@save`
  - Restrição: `{appointment}` é UUID.

- `POST /workspace/{slug}/appointments/online/{appointment}/send-email`
  - Name: `tenant.online-appointments.send-email`
  - Controller: `Tenant\\OnlineAppointmentController@sendEmail`
  - Restrição: `{appointment}` é UUID.

- `POST /workspace/{slug}/appointments/online/{appointment}/send-whatsapp`
  - Name: `tenant.online-appointments.send-whatsapp`
  - Controller: `Tenant\\OnlineAppointmentController@sendWhatsapp`
  - Restrição: `{appointment}` é UUID.
