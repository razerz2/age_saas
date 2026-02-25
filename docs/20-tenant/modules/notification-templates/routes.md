# Routes

## Editor (Settings > Editor)

Arquivo: `routes/tenant.php`

Entrada (aba do Settings via querystring):

- `GET /workspace/{slug}/settings?tab=editor&channel=email&key=appointment.pending_confirmation`

Endpoints (POST) do Editor:

- `POST /workspace/{slug}/settings/editor/save` -> `SettingsController@updateNotificationTemplate`
  - name: `tenant.settings.editor.save`
- `POST /workspace/{slug}/settings/editor/restore` -> `SettingsController@restoreNotificationTemplate`
  - name: `tenant.settings.editor.restore`
- `POST /workspace/{slug}/settings/editor/preview` -> `SettingsController@previewNotificationTemplate`
  - name: `tenant.settings.editor.preview`

## Links públicos usados em templates

Arquivo: `routes/tenant.php`

- `GET /customer/{slug}/agendamento/confirm/{token}` -> `PublicAppointmentController@confirmByToken`
  - name: `public.appointment.confirm`
- `POST /customer/{slug}/agendamento/cancel/{token}` -> `PublicAppointmentController@cancelByToken`
  - name: `public.appointment.cancel`
- `GET /customer/{slug}/agendamento/{appointment_id}` -> `PublicAppointmentController@show`
  - name: `public.appointment.show`
- `GET /customer/{slug}/agendamento/oferta/{token}` -> `PublicAppointmentWaitlistController@showOffer`
  - name: `public.waitlist.offer.show`
- `POST /customer/{slug}/agendamento/oferta/{token}/accept` -> `PublicAppointmentWaitlistController@acceptOffer`
  - name: `public.waitlist.offer.accept`

