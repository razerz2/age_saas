# Routes

## Prefixos

- Interno autenticado: `/workspace/{slug}` (grupo `tenant.*`)
- Público: `/customer/{slug}` (grupo `public.*`)

## Rotas internas (appointments)

Arquivo: `routes/tenant.php`

- `POST /workspace/{slug}/appointments` -> `AppointmentController@store`
- `POST /workspace/{slug}/appointments/{appointment}/confirm` -> `AppointmentController@confirm`
- `POST /workspace/{slug}/appointments/{appointment}/cancel` -> `AppointmentController@cancel`
- `POST /workspace/{slug}/appointments/waitlist` -> `AppointmentWaitlistController@store`

Slots API interno:

- `GET /workspace/{slug}/api/doctors/{doctorId}/available-slots` -> `AppointmentController@getAvailableSlots`

## Rotas públicas (agendamento)

Arquivo: `routes/tenant.php`

- `GET /customer/{slug}/agendamento/criar` -> `PublicAppointmentController@create`
- `POST /customer/{slug}/agendamento/criar` -> `PublicAppointmentController@store`
- `GET /customer/{slug}/agendamento/sucesso/{appointment_id?}` -> `PublicAppointmentController@success`
- `GET /customer/{slug}/agendamento/{appointment_id}` -> `PublicAppointmentController@show`
- `GET /customer/{slug}/agendamento/confirm/{token}` -> `PublicAppointmentController@confirmByToken`
- `POST /customer/{slug}/agendamento/cancel/{token}` -> `PublicAppointmentController@cancelByToken`

Slots API público:

- `GET /customer/{slug}/agendamento/api/doctors/{doctorId}/available-slots` -> `PublicAppointmentController@getAvailableSlots`

## Rotas públicas de waitlist/oferta

Arquivo: `routes/tenant.php`

- `POST /customer/{slug}/agendamento/waitlist` -> `PublicAppointmentWaitlistController@store`
- `GET /customer/{slug}/agendamento/oferta/{token}` -> `PublicAppointmentWaitlistController@showOffer`
- `POST /customer/{slug}/agendamento/oferta/{token}/accept` -> `PublicAppointmentWaitlistController@acceptOffer`

## Rota de settings relacionada

Arquivo: `routes/tenant.php`

- `POST /workspace/{slug}/settings/appointments` -> `SettingsController@updateAppointments`

## Observação de recorrência

- Rotas de recorrência continuam em namespace/paths próprios (`/workspace/{slug}/agendamentos/recorrentes*`).
- Não há rota nova de hold/waitlist dentro do módulo recorrente.

## Notificações (templates)

- As notificações enviadas ao paciente (email/whatsapp) usam keys do catálogo e podem ser personalizadas no Editor.
- Veja: `docs/20-tenant/modules/notification-templates/overview.md`.

