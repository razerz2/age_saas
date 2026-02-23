# Medical Appointments — Views

Fontes: `MedicalAppointmentController` e documentação em `TENANT.md`.

## Views principais

- `resources/views/tenant/medical_appointments/index.blade.php`
  - Tela inicial para seleção de data e, quando aplicável, médicos para iniciar a sessão de atendimento.

- `resources/views/tenant/medical_appointments/session.blade.php`
  - Sessão de atendimento do dia, listando agendamentos filtrados por permissões do usuário.

## Partials

- `resources/views/tenant/medical_appointments/partials/details.blade.php`
  - Modal/partial com detalhes do agendamento (paciente, médico, tipo de consulta, etc.).

- `resources/views/tenant/medical_appointments/partials/form-response-modal.blade.php`
  - Modal para exibir respostas de formulários vinculados ao agendamento.

- `resources/views/tenant/medical_appointments/partials/error.blade.php`
  - Partial usada para exibir erros ao carregar detalhes ou respostas de formulário.

## Observações

- As views seguem o padrão de layout do Tenant (cards, grids, etc.), conforme documentado em `TENANT.md`.
- Padrões globais de UI em `docs/00-global/03-padroes-frontend.md`.
