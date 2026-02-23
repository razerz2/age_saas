# Public Customer — Troubleshooting

## Erros e mensagens reais

- Mensagens específicas de erro dos controllers públicos não foram inspecionadas nesta etapa.
- (não identificado no código — recomenda-se abrir cada controller público para mapear mensagens de validação e fluxo de erro).

## Checklist genérico (marcado como genérico)

- Verificar se o domínio/path aponta corretamente para `/customer/{slug}` com `slug` de Tenant válido.
- Confirmar se `tenant-web` está resolvendo corretamente o Tenant.
- Validar se as migrations de pacientes, agendamentos e formulários foram executadas.
- Verificar se APIs públicas de agendamento retornam dados esperados (doctors, calendars, appointment-types, available-slots, business-hours).
- Em caso de falhas 4xx/5xx, revisar logs dos controllers públicos (`PublicPatientController`, `PublicPatientRegisterController`, `PublicAppointmentController`, `PublicFormController`).
