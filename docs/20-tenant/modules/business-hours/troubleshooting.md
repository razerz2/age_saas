# Business Hours — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `BusinessHourController`.

- "Você não tem permissão para realizar esta ação."
  - Situação: usuário comum tentando criar horários sem um médico permitido ou sem `doctor_id` válido.
  - Ação sugerida: verificar role e configuração de médicos permitidos.

- "Médico não encontrado."
  - Situação: após resolver `doctor` em `store`, o médico não é encontrado.
  - Ação sugerida: confirmar que o `doctor_id` existe e está ativo.

- Mensagens genéricas de sucesso:
  - "Horário de atendimento criado com sucesso para X dia(s)."
  - "Nenhum horário foi criado. Os horários selecionados já existem."
  - "Horário atualizado com sucesso."
  - "Horário removido."

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado no guard `tenant`.
- Confirmar se o usuário possui médicos vinculados (para `role = user`).
- Verificar se os campos `weekday`, `start_time`, `end_time` estão corretos e dentro de intervalos válidos.
- Conferir se migrations de `business_hours` foram executadas (`database/migrations/tenant/*business_hours*`).
- Revisar logs de aplicação para erros de permissão ou validação ligados a BusinessHour.
