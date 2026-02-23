# Medical Appointments — Rotas

Fontes: `routes/tenant.php` (bloco de atendimento médico / medical appointments).

## Grupo principal

- Prefixo autenticado: `/workspace/{slug}`
- Nome base: `tenant.`
- Middlewares de grupo (conforme `routes/tenant.php`):
  - `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`.

## Rotas do módulo

> Observação: os nomes exatos das rotas podem variar; aqui estão descritos conforme o padrão documentado em `TENANT.md` e o comportamento do `MedicalAppointmentController`.

- `GET /workspace/{slug}/atendimento`
  - Name: `tenant.medical-appointments.index` (conforme documentação em `TENANT.md`).
  - Controller: `Tenant\\MedicalAppointmentController@index`

- `POST /workspace/{slug}/atendimento/iniciar`
  - Name: `tenant.medical-appointments.start`
  - Controller: `Tenant\\MedicalAppointmentController@start`

- `GET /workspace/{slug}/atendimento/dia/{date}`
  - Name: `tenant.medical-appointments.session`
  - Controller: `Tenant\\MedicalAppointmentController@session`

- `GET /workspace/{slug}/atendimento/{appointment}/detalhes`
  - Name: `tenant.medical-appointments.details`
  - Controller: `Tenant\\MedicalAppointmentController@details`

- `POST /workspace/{slug}/atendimento/{appointment}/status`
  - Name: `tenant.medical-appointments.update-status`
  - Controller: `Tenant\\MedicalAppointmentController@updateStatus`

- `POST /workspace/{slug}/atendimento/{appointment}/concluir`
  - Name: `tenant.medical-appointments.complete`
  - Controller: `Tenant\\MedicalAppointmentController@complete`

- `GET /workspace/{slug}/atendimento/{appointment}/form-response`
  - Name: `tenant.medical-appointments.form-response`
  - Controller: `Tenant\\MedicalAppointmentController@getFormResponse`
