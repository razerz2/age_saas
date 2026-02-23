# Medical Appointments — Backend

Fontes: `app/Http/Controllers/Tenant/MedicalAppointmentController.php`.

## Controller

- `App\Http\Controllers\Tenant\MedicalAppointmentController`
  - Métodos públicos (parcialmente listados pelos trechos inspecionados):
    - `index()` — tela inicial de seleção de data/médico.
    - `start(Request $request)` — processa data/médicos e redireciona para a sessão.
    - `session($date, Request $request)` — exibe sessão de atendimento do dia.
    - `details($appointment)` — retorna detalhes de um agendamento (HTML/JSON) para modal.
    - `updateStatus(Request $request, Appointment $appointment)` — atualiza status do agendamento.
    - `complete(Appointment $appointment)` — conclui atendimento e redireciona para o próximo.
    - `getFormResponse($appointmentId)` — retorna resposta de formulário em JSON/HTML.

## Models

- `App\Models\Tenant\Appointment`
- `App\Models\Tenant\Doctor`
- `App\Models\Tenant\Form`
- `App\Models\Tenant\FormResponse`
- `App\Models\Platform\Tenant`

## Fluxo geral (factual)

- `index`:
  - Obtém o usuário autenticado via `Auth::guard('tenant')`.
  - Para `role = admin` ou `user`, carrega lista de médicos permitidos (via relações com `Doctor`).
  - Para `role = doctor`, utiliza automaticamente o médico vinculado.
  - Retorna a view `tenant.medical_appointments.index` com lista de médicos (quando aplicável).

- `start`:
  - Valida data e, quando necessário, lista de `doctor_ids`.
  - Para `role = doctor`, força uso do médico do usuário.
  - Para `role = user`, valida que `doctor_ids` estão na lista de médicos permitidos.
  - Redireciona para rota da sessão (`.../atendimento/dia/{date}`) com `doctor_ids` na query string.

- `session`:
  - Garante conexão correta do Tenant via `ensureTenantConnection()`.
  - Carrega agendamentos do dia (`Appointment::forDay(...)`) com `calendar.doctor.user`, `patient`, `type`, `specialty`.
  - Filtra agendamentos de acordo com `role` e permissões (admin, doctor, user com `allowedDoctors`).
  - Retorna view `tenant.medical_appointments.session` com coleção de agendamentos.

- `details`:
  - Garante conexão de Tenant.
  - Carrega `Appointment` (com `calendar.doctor.user`, `patient`, `type`, `specialty`).
  - Verifica permissões de acesso ao agendamento (médico/usuário permitido).
  - Retorna view `tenant.medical_appointments.partials.details` ou JSON de erro, conforme `request()->ajax()`/`wantsJson()`.

- `updateStatus` / `complete`:
  - Garante conexão de Tenant.
  - Verifica permissão via método privado `checkPermission($user, Appointment $appointment)`.
  - Atualiza `status` do agendamento (`scheduled`, `arrived`, `in_service`, `completed`, `cancelled`).
  - Em `complete`, busca próximo agendamento do dia com base em filtros de permissão.

- `getFormResponse`:
  - Garante conexão de Tenant.
  - Verifica permissão via `checkPermission`.
  - Usa `Form::getFormForAppointment($appointment)` e `FormResponse` para carregar última resposta submetida.
  - Retorna JSON com `html` renderizado pela view `tenant.medical_appointments.partials.form-response-modal` ou mensagens de erro JSON.
