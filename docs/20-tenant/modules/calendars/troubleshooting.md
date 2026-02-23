# Calendars — Troubleshooting

## Erros e mensagens reais (do código)

Fontes: `CalendarController`.

- "Você já possui um calendário cadastrado. Cada médico pode ter apenas um calendário."
  - Situação: tentativa de criar novo calendário para médico que já possui.
  - Ação sugerida: revisar os calendários existentes antes de criar outro.

- "Este médico já possui um calendário cadastrado. Cada médico pode ter apenas um calendário."
  - Situação: ao alterar o médico de um calendário para outro que já tem calendário.
  - Ação sugerida: manter um único calendário por médico.

- "Você não tem permissão para visualizar/editar/remover este calendário."
  - Situação: usuário tenta acessar calendário de médico para o qual não possui permissão.
  - Ação sugerida: verificar role (`admin/doctor/user`) e lista de médicos permitidos.

- "Nenhum calendário encontrado. Crie um calendário primeiro."
  - Situação: `eventsRedirect` não encontra calendário adequado para o usuário.
  - Ação sugerida: criar calendário para o médico atual.

## Checklist genérico (marcado como genérico)

- Verificar se o usuário está autenticado com o guard `tenant`.
- Confirmar se o médico está ativo e tem relação correta com o usuário (roles e `allowedDoctors`).
- Verificar se o médico não possui mais de um calendário.
- Conferir se migrations de calendário foram executadas (`database/migrations/tenant/*calendar*`).
- Revisar logs de aplicação quando houver aborts 403/404 ligados a calendários.
