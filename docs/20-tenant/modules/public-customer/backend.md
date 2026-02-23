# Public Customer — Backend

Fontes: controllers públicos do Tenant.

## Controllers

- `App\Http\Controllers\Tenant\PublicPatientController`
  - Métodos (resumidos a partir de `routes/tenant.php`):
    - `showIdentify()` — formulário de identificação do paciente.
    - `identify()` — processamento da identificação do paciente.

- `App\Http\Controllers\Tenant\PublicPatientRegisterController`
  - Métodos:
    - `showRegister()` — formulário de cadastro de paciente.
    - `register()` — processamento do cadastro.

- `App\Http\Controllers\Tenant\PublicAppointmentController`
  - Métodos (a partir de rotas públicas e APIs):
    - `create()` — exibe tela de criação de agendamento público.
    - `store()` — processa criação do agendamento.
    - `success()` — página de sucesso.
    - `show()` — visualização de um agendamento.
    - `getCalendarsByDoctor()` — API de calendários.
    - `getAppointmentTypesByDoctor()` — API de tipos.
    - `getSpecialtiesByDoctor()` — API de especialidades.
    - `getAvailableSlots()` — API de horários disponíveis.
    - `getBusinessHoursByDoctor()` — API de horários comerciais.

- `App\Http\Controllers\Tenant\PublicFormController`
  - Métodos:
    - `create()` — exibe formulário público para resposta.
    - `store()` — salva resposta.
    - `success()` — página de sucesso da resposta.

## Models

- `App/Models/Tenant/Patient.php`
- `App/Models/Tenant/Appointment.php`
- `App/Models/Tenant/Form.php`
- `App/Models/Tenant/FormResponse.php`

(Models exatos usados em cada controller devem ser confirmados abrindo os arquivos — aqui foram listados os mais prováveis a partir das rotas e domínio.)

## Observações

- Os controllers públicos operam sob middleware `tenant-web` e não usam o guard `tenant` autenticado.
- A validação de dados e uso de Requests específicos não foi inspecionada em detalhe.
- (não identificado no código — recomenda-se abrir cada controller para mapear Requests, Services e regras de negócio com mais precisão).
