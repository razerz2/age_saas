# Public Customer — Rotas

Fontes: `routes/tenant.php` (prefixo `/customer/{slug}` com middleware `tenant-web`).

## Grupo principal

- Prefixo público do Tenant: `/customer/{slug}`
- Alias de rotas: `public.` (para agendamento) e `tenant.` (para login público).
- Middleware principal: `tenant-web`.

## Rotas de agendamento público

- `GET /customer/{slug}/agendamento/identificar`
  - Name: `public.patient.identify`
  - Controller: `Tenant\\PublicPatientController@showIdentify`

- `POST /customer/{slug}/agendamento/identificar`
  - Name: `public.patient.identify.submit`
  - Controller: `Tenant\\PublicPatientController@identify`

- `GET /customer/{slug}/agendamento/cadastro`
  - Name: `public.patient.register`
  - Controller: `Tenant\\PublicPatientRegisterController@showRegister`

- `POST /customer/{slug}/agendamento/cadastro`
  - Name: `public.patient.register.submit`
  - Controller: `Tenant\\PublicPatientRegisterController@register`

- `GET /customer/{slug}/agendamento/criar`
  - Name: `public.appointment.create`
  - Controller: `Tenant\\PublicAppointmentController@create`

- `POST /customer/{slug}/agendamento/criar`
  - Name: `public.appointment.store`
  - Controller: `Tenant\\PublicAppointmentController@store`

- `GET /customer/{slug}/agendamento/sucesso/{appointment_id?}`
  - Name: `public.appointment.success`
  - Controller: `Tenant\\PublicAppointmentController@success`

- `GET /customer/{slug}/agendamento/{appointment_id}`
  - Name: `public.appointment.show`
  - Controller: `Tenant\\PublicAppointmentController@show`

## APIs públicas de agendamento

Prefixo: `/customer/{slug}/agendamento/api`

- `GET /doctors/{doctorId}/calendars`
  - Name: `public.api.calendars`
  - Controller: `Tenant\\PublicAppointmentController@getCalendarsByDoctor`

- `GET /doctors/{doctorId}/appointment-types`
  - Name: `public.api.appointment-types`
  - Controller: `Tenant\\PublicAppointmentController@getAppointmentTypesByDoctor`

- `GET /doctors/{doctorId}/specialties`
  - Name: `public.api.specialties`
  - Controller: `Tenant\\PublicAppointmentController@getSpecialtiesByDoctor`

- `GET /doctors/{doctorId}/available-slots`
  - Name: `public.api.available-slots`
  - Controller: `Tenant\\PublicAppointmentController@getAvailableSlots`

- `GET /doctors/{doctorId}/business-hours`
  - Name: `public.api.business-hours`
  - Controller: `Tenant\\PublicAppointmentController@getBusinessHoursByDoctor`

## Formulários públicos

- `GET /customer/{slug}/formulario/{form}/responder`
  - Name: `public.form.response.create`
  - Controller: `Tenant\\PublicFormController@create`

- `POST /customer/{slug}/formulario/{form}/responder`
  - Name: `public.form.response.store`
  - Controller: `Tenant\\PublicFormController@store`

- `GET /customer/{slug}/formulario/{form}/resposta/{response}/sucesso`
  - Name: `public.form.response.success`
  - Controller: `Tenant\\PublicFormController@success`
