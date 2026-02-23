# Online Appointments — Backend

Fontes: `app/Http/Controllers/Tenant/OnlineAppointmentController.php`.

## Controller

- `App\Http\Controllers\Tenant\OnlineAppointmentController`
  - Métodos públicos:
    - `index()`
    - `show($slug, $id)`
    - `save(Request $request, $slug, $id)`
    - `sendEmail(Request $request, $slug, $id)`
    - `sendWhatsapp(Request $request, $slug, $id)`
    - `gridData(Request $request, $slug)`

## Models e serviços

- `App\Models\Tenant\Appointment`
- `App\Models\Tenant\OnlineAppointmentInstruction`
- `App\Models\Tenant\TenantSetting`
- `App\Services\MailTenantService`
- `App\Services\WhatsappTenantService`

## Fluxo geral (factual)

- `index`:
  - Verifica modo padrão de agendamento via `TenantSetting::get('appointments.default_appointment_mode', 'user_choice')`.
  - Se modo for `presencial`, aborta com 404.
  - Lista apenas `Appointment` com `appointment_mode = 'online'`, carregando `patient`, `calendar.doctor.user`, `type`, `specialty`, `onlineInstructions`.

- `show`:
  - Verifica modo (`presencial` → 404) e se o agendamento é online; caso contrário, aborta 403.
  - Carrega `Appointment` com `patient`, `calendar.doctor.user`, `type`, `specialty`, `onlineInstructions`.
  - Se não existir `onlineInstructions`, cria um registro vazio em `OnlineAppointmentInstruction` e recarrega.
  - Avalia permissões de envio (`notifications.send_email_to_patients`, `notifications.send_whatsapp_to_patients`) via `TenantSetting::getAll()`.

- `save`:
  - Verifica modo e tipo de consulta conforme descrito acima.
  - Valida campos `meeting_link`, `meeting_app`, `general_instructions`, `patient_instructions`.
  - Cria ou atualiza `OnlineAppointmentInstruction` vinculada ao agendamento.

- `sendEmail`:
  - Carrega `Appointment` com `patient` e `onlineInstructions`.
  - Verifica se envio de e‑mail está habilitado (`TenantSetting::getAll()`), se paciente possui e‑mail e se existem instruções.
  - Usa `MailTenantService::send(...)` para enviar as instruções.
  - Atualiza `sent_by_email_at` em `OnlineAppointmentInstruction` e grava logs de erro em caso de exceção.

- `sendWhatsapp`:
  - Verifica modo, e se agendamento é online.
  - Verifica se envio de WhatsApp está habilitado, se paciente possui telefone e se existem instruções.
  - Monta mensagem com dados do paciente, data/hora e instruções e envia via `WhatsappTenantService::send(...)`.
  - Atualiza `sent_by_whatsapp_at` em `OnlineAppointmentInstruction` e grava logs em caso de erro.

- `gridData`:
  - Retorna JSON para Grid.js com colunas `patient`, `doctor`, `datetime`, `status_badge`, `instructions`, `actions`.
  - Usa partials Blade em `tenant.online_appointments.partials.*` para colunas HTML.
