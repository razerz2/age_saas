# Online Appointments — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `OnlineAppointmentController`.

- "Esta consulta não é online."
  - Situação: tentativa de acessar `show/save/sendEmail/sendWhatsapp` para um agendamento com `appointment_mode` diferente de `online`.

- "Envio de email aos pacientes está desabilitado nas configurações."
  - Situação: `sendEmail` verifica `TenantSetting` e encontra `notifications.send_email_to_patients` desabilitado.

- "O paciente não possui email cadastrado."
  - Situação: `sendEmail` valida existência de e‑mail no paciente.

- "Configure as instruções antes de enviar."
  - Situação: tentativa de enviar email/WhatsApp sem `onlineInstructions` configuradas.

- "Envio de WhatsApp aos pacientes está desabilitado nas configurações."
  - Situação: `sendWhatsapp` com configuração de WhatsApp desabilitada.

- "O paciente não possui telefone cadastrado."
  - Situação: `sendWhatsapp` valida existência de telefone no paciente.

- Mensagens de erro genéricas (via logs):
  - Logs de erro ao enviar email/WhatsApp são registrados com contexto (`appointment_id`, mensagem de erro).

## Checklist genérico (marcado como genérico)

- Verificar se o modo padrão de agendamento (`appointments.default_appointment_mode`) permite consultas online.
- Conferir se o agendamento possui `appointment_mode = 'online'`.
- Confirmar se `TenantSetting` tem `notifications.send_email_to_patients` e/ou `notifications.send_whatsapp_to_patients` habilitados.
- Verificar se o paciente possui e‑mail/telefone cadastrados.
- Verificar se existe registro em `OnlineAppointmentInstruction` e se os campos foram preenchidos.
- Revisar logs para exceções na integração de e‑mail/WhatsApp.
