# Medical Appointments — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `MedicalAppointmentController`.

- Erros de permissão (403) registrados em log:
  - Usuário com `role = doctor` sem vínculo com `Doctor`.
  - Usuário com `role = user` tentando acessar médicos não permitidos.

- Erros de conexão de Tenant:
  - `ensureTenantConnection()` registra problemas ao restaurar conexão do Tenant se a configuração estiver inconsistente.

- Erros ao carregar detalhes ou resposta de formulário:
  - `getFormResponse` retorna JSON com `success = false` e mensagem quando:
    - Não há formulário associado ao agendamento.
    - Não há resposta submetida para o formulário/agendamento.
    - Ocorre exceção ao carregar resposta (registrada em log).

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado no guard `tenant` e possui `role` correta.
- Confirmar que médicos estão corretamente associados a usuários (`doctor` para `role = doctor`, `allowedDoctors` para `role = user`).
- Verificar se migrations de `appointments`, `forms` e `form_responses` foram executadas.
- Revisar logs quando houver aborts 403/404/500 ligados a `MedicalAppointmentController`.
- Validar se integrações de formulário estão configuradas (existência de `Form` e `FormResponse` adequados para o agendamento).
