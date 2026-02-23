# Appointment Types — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `AppointmentTypeController`.

- "Você não tem permissão para realizar esta ação."
  - Situação: usuário comum tenta criar tipo de atendimento sem médico permitido ou sem `doctor_id` válido.

- "Médico não encontrado."
  - Situação: após resolver o médico em `store`, o médico não é encontrado.

- "Você não tem permissão para atualizar este tipo de atendimento."
  - Situação: `update` valida que o médico associado não está na lista de médicos permitidos para o usuário.

- "Você não tem permissão para remover este tipo de atendimento."
  - Situação: `destroy` valida permissão semelhante a `update`.

- Mensagens genéricas de sucesso:
  - "Tipo de atendimento criado com sucesso."
  - "Tipo de atendimento atualizado com sucesso."
  - "Tipo removido."

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado no guard `tenant`.
- Confirmar se o usuário possui médicos vinculados e permitidos (`allowedDoctors`).
- Verificar se os campos `duration_min`, `price` e `doctor_id` estão corretos.
- Conferir se migrations de `appointment_types` foram executadas (`database/migrations/tenant/*appointment_types*`).
- Revisar logs em casos de abort 403/404 ou erros de validação ligados a tipos de atendimento.
